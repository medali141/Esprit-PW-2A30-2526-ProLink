<?php
/**
 * Helper email participation — génère ticket PDF si confirmé, envoie en pièce jointe.
 */
require_once __DIR__ . '/mail.php';

// ── Charge SmtpSimpleClient ────────────────────────────────────────────────
$_clientPath = __DIR__ . '/../lib/SmtpSimpleClient.php';
if (!is_file($_clientPath)) {
    die('[mailer.php] ERREUR : SmtpSimpleClient.php introuvable dans lib/.');
}
if (!class_exists('SmtpSimpleClient')) {
    require_once $_clientPath;
}

// ── Charge SmtpSimpleClientAttachment ─────────────────────────────────────
$_attachPath = __DIR__ . '/../lib/SmtpSimpleClientAttachment.php';
if (!is_file($_attachPath)) {
    die('[mailer.php] ERREUR : SmtpSimpleClientAttachment.php introuvable dans lib/.');
}
if (!class_exists('SmtpSimpleClientAttachment')) {
    require_once $_attachPath;
}

// ── Charge TicketGenerator (nécessite lib/fpdf.php) ────────────────────────
$_fpdfPath   = __DIR__ . '/../lib/fpdf.php';
$_ticketPath = __DIR__ . '/../lib/TicketGenerator.php';
if (is_file($_fpdfPath) && is_file($_ticketPath)) {
    if (!class_exists('FPDF')) {
        require_once $_fpdfPath;
    }
    if (!class_exists('TicketGenerator')) {
        require_once $_ticketPath;
    }
}

function sendParticipationEmail(
    string $toEmail,
    string $toName,
    string $eventTitle,
    string $statut,
    array  $participationData = []
): bool {
    if (!defined('PROLINK_SMTP_HOST') || PROLINK_SMTP_HOST === '') {
        error_log('[ParticipationMail] SMTP non configuré');
        return false;
    }

    if ($statut === 'confirmé') {
        $subject = 'Participation confirmee - ' . $eventTitle;
        $html    = "
            <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;
                        border:1px solid #d1fae5;border-radius:12px;overflow:hidden;'>
                <div style='background:#0f173c;padding:20px;text-align:center;'>
                    <h1 style='color:#6c4daf;margin:0;font-size:20px;'>PROLINK</h1>
                    <p style='color:#a0a0c0;margin:4px 0 0;font-size:12px;'>Gestion d'evenements</p>
                </div>
                <div style='padding:24px;'>
                    <h2 style='color:#065f46;'>Participation confirmee !</h2>
                    <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>Votre participation a l'evenement
                       <strong style='color:#6c4daf;'>" . htmlspecialchars($eventTitle) . "</strong>
                       a ete <strong style='color:#065f46;'>confirmee</strong>.</p>
                    <p style='background:#f0f0fa;border-radius:8px;padding:12px;'>
                        Votre ticket est joint a cet email en PDF.<br>
                        Presentez-le a l'entree de l'evenement.
                    </p>
                    <p>Nous sommes ravis de vous compter parmi nous. A bientot !</p>
                </div>
                <div style='background:#f9f9ff;padding:12px;text-align:center;'>
                    <p style='color:#9ca3af;font-size:11px;margin:0;'>Email automatique — ProLink. Ne pas repondre.</p>
                </div>
            </div>";
        $alt = "Bonjour {$toName}, votre participation a {$eventTitle} est confirmee. Votre ticket est joint en PDF.";

        $ticketPath = '';
        $pid = (int)($participationData['id_participation'] ?? 0);
        if ($pid > 0 && class_exists('TicketGenerator')) {
            try {
                $ticketPath = TicketGenerator::generate($participationData);
            } catch (Throwable $e) {
                error_log('[TicketGenerator] ' . $e->getMessage());
            }
        }

        $ticketName = 'Ticket_' . TicketGenerator::buildCode($pid) . '.pdf';

        if ($ticketPath && is_file($ticketPath)) {
            $result = SmtpSimpleClientAttachment::sendWithAttachment(
                PROLINK_MAIL_FROM, PROLINK_MAIL_FROM_NAME,
                $toEmail, $subject, $html, $alt,
                PROLINK_SMTP_HOST, (int) PROLINK_SMTP_PORT,
                PROLINK_SMTP_USER, PROLINK_SMTP_PASS, PROLINK_SMTP_SECURE,
                $ticketPath, $ticketName, 'application/pdf'
            );
            @unlink($ticketPath);
            return $result;
        }

        // Fallback sans pièce jointe
        return SmtpSimpleClient::sendHtml(
            PROLINK_MAIL_FROM, PROLINK_MAIL_FROM_NAME,
            $toEmail, $subject, $html, $alt,
            PROLINK_SMTP_HOST, (int) PROLINK_SMTP_PORT,
            PROLINK_SMTP_USER, PROLINK_SMTP_PASS, PROLINK_SMTP_SECURE
        );

    } else {
        // ── Email de refus ─────────────────────────────────────
        $subject = 'Participation refusee - ' . $eventTitle;
        $html    = "
            <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;
                        border:1px solid #fee2e2;border-radius:12px;overflow:hidden;'>
                <div style='background:#0f173c;padding:20px;text-align:center;'>
                    <h1 style='color:#6c4daf;margin:0;font-size:20px;'>PROLINK</h1>
                    <p style='color:#a0a0c0;margin:4px 0 0;font-size:12px;'>Gestion d'evenements</p>
                </div>
                <div style='padding:24px;'>
                    <h2 style='color:#991b1b;'>Participation refusee</h2>
                    <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>Votre participation a l'evenement
                       <strong style='color:#6c4daf;'>" . htmlspecialchars($eventTitle) . "</strong>
                       a ete <strong style='color:#991b1b;'>refusee</strong>.</p>
                    <p>Pour toute question, contactez l'organisateur.</p>
                </div>
                <div style='background:#f9f9ff;padding:12px;text-align:center;'>
                    <p style='color:#9ca3af;font-size:11px;margin:0;'>Email automatique — ProLink. Ne pas repondre.</p>
                </div>
            </div>";
        $alt = "Bonjour {$toName}, votre participation a {$eventTitle} a ete refusee.";

        return SmtpSimpleClient::sendHtml(
            PROLINK_MAIL_FROM, PROLINK_MAIL_FROM_NAME,
            $toEmail, $subject, $html, $alt,
            PROLINK_SMTP_HOST, (int) PROLINK_SMTP_PORT,
            PROLINK_SMTP_USER, PROLINK_SMTP_PASS, PROLINK_SMTP_SECURE
        );
    }
}