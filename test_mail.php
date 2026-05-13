<?php
/**
 * test_mail.php - Test de connexion SMTP
 * Accédez à: http://localhost/Esprit-PW-2A30-2526-ProLink-main1/test_mail.php
 */

require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/lib/MailOtpService.php';

echo "=== TEST DE CONNEXION SMTP ===\n\n";

// Affiche les paramètres
echo "Configuration:\n";
echo "- From: " . (defined('PROLINK_MAIL_FROM') ? PROLINK_MAIL_FROM : 'NON DÉFINI') . "\n";
echo "- SMTP Host: " . (defined('PROLINK_SMTP_HOST') ? PROLINK_SMTP_HOST : 'NON DÉFINI') . "\n";
echo "- SMTP Port: " . (defined('PROLINK_SMTP_PORT') ? PROLINK_SMTP_PORT : 'NON DÉFINI') . "\n";
echo "- SMTP User: " . (defined('PROLINK_SMTP_USER') ? PROLINK_SMTP_USER : 'NON DÉFINI') . "\n";
echo "- SMTP Secure: " . (defined('PROLINK_SMTP_SECURE') ? PROLINK_SMTP_SECURE : 'NON DÉFINI') . "\n\n";

// Teste l'envoi
if (!defined('PROLINK_SMTP_HOST') || PROLINK_SMTP_HOST === '') {
    echo "❌ SMTP non configuré dans mail.local.php\n";
    exit(1);
}

$toEmail = 'ghaidalemjid8@gmail.com';
$subject = 'Test ProLink - ' . date('Y-m-d H:i:s');
$html = '<p>Email de test ProLink</p><p>Si vous recevez cet email, SMTP fonctionne! ✓</p>';
$alt = 'Email de test ProLink';

echo "Envoi d'un email de test à: $toEmail\n";
echo "Sujet: $subject\n\n";

$result = MailOtpService::sendPasswordResetOtp($toEmail, '123456', 900);

if ($result) {
    echo "✅ Email envoyé avec succès!\n";
    echo "Vérifiez votre boîte Gmail (et les spams).\n";
} else {
    echo "❌ Impossible d'envoyer l'email.\n";
    echo "Vérifiez:\n";
    echo "1. Que mail.local.php contient les bonnes identifiants\n";
    echo "2. Que le mot de passe d'application est correct (sans espaces dans le code)\n";
    echo "3. Que 2FA est activé sur votre compte Gmail\n";
    echo "4. Les logs PHP pour les erreurs détaillées\n";
}
