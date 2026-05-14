<?php
/**
 * Génère un ticket PDF pour une participation confirmée.
 * Requiert : lib/fpdf.php (téléchargeable sur fpdf.org)
 */
if (!class_exists('FPDF')) {
    require_once __DIR__ . '/fpdf.php';
}
if (class_exists('TicketGenerator')) {
    return; // déjà chargé
}

class TicketGenerator extends FPDF
{
    private array $navy   = [15,  23,  60];
    private array $purple = [108, 77,  175];
    private array $white  = [255, 255, 255];
    private array $light  = [240, 240, 250];
    private array $dark   = [30,  30,  50];

    /**
     * Construit les données encodées dans le QR code.
     * Contenu : CODE|EVENT|NOM|HASH_SHA256
     * → Le hash garantit que chaque ticket est unique et vérifiable.
     */
    public static function buildQrData(array $data): string
    {
        $pid   = (int)($data['id_participation'] ?? 0);
        $event = (string)($data['titre_event']   ?? '');
        $nom   = trim((string)($data['prenom'] ?? '') . ' ' . (string)($data['nom'] ?? ''));
        $code  = self::buildCode($pid);
        $hash  = strtoupper(substr(hash('sha256', $code . '|' . $event . '|' . $nom . '|prolink_secret_2025'), 0, 16));
        return $code . '|' . $event . '|' . $nom . '|' . $hash;
    }

    public static function buildCode(int $pid): string
    {
        return 'PRT-' . date('Y') . '-' . str_pad((string)$pid, 4, '0', STR_PAD_LEFT);
    }

    public static function buildSecurityCode(string $code, string $nom, string $event): string
    {
        $raw = strtoupper(substr(hash('sha256', $code . $nom . $event . 'prolink_sec'), 0, 12));
        return substr($raw, 0, 4) . '-' . substr($raw, 4, 4) . '-' . substr($raw, 8, 4);
    }

    /**
     * Génère le PDF et retourne son chemin temporaire.
     *
     * @param array $data { id_participation, nom, prenom, titre_event, date_debut, date_fin, lieu_event }
     */
    public static function generate(array $data): string
    {
        $pdf = new self('L', 'mm', [210, 100]);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->build($data);

        $pid  = (int)($data['id_participation'] ?? 0);
        $path = sys_get_temp_dir() . '/ticket_' . $pid . '_' . time() . '.pdf';
        $pdf->Output('F', $path);
        return $path;
    }

    private function build(array $d): void
    {
        $nom   = $this->clean(trim((string)($d['prenom'] ?? '') . ' ' . (string)($d['nom'] ?? '')));
        $event = $this->clean((string)($d['titre_event'] ?? ''));
        $debut = $this->formatDate((string)($d['date_debut'] ?? ''));
        $fin   = $this->formatDate((string)($d['date_fin']   ?? ''));
        $lieu  = $this->clean((string)($d['lieu_event'] ?? ''));
        $pid   = (int)($d['id_participation'] ?? 0);

        $code   = self::buildCode($pid);
        $secFmt = self::buildSecurityCode($code, $nom, $event);
        $qrData = self::buildQrData($d); // ← unique par participation

        $W = 210; $H = 100;
        $leftW = 52; $rightW = 48; $midW = $W - $leftW - $rightW;

        // ── PANNEAU GAUCHE (navy) ─────────────────────────────
        $this->SetFillColor(...$this->navy);
        $this->Rect(0, 0, $leftW, $H, 'F');

        $this->SetTextColor(...$this->purple);
        $this->SetFont('Helvetica', 'B', 7);
        $this->SetXY(3, 6);
        $this->Cell($leftW - 6, 5, 'TICKET PARTICIPANT', 0, 1, 'C');

        $this->SetFillColor(...$this->purple);
        $this->Rect(8, 12, $leftW - 16, 1, 'F');

        $words = explode(' ', $event);
        $this->SetTextColor(...$this->white);
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetXY(3, 16);
        $this->Cell($leftW - 6, 7, $words[0] ?? '', 0, 1, 'C');
        if (count($words) > 1) {
            $this->SetTextColor(...$this->purple);
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetXY(3, 22);
            $this->Cell($leftW - 6, 7, implode(' ', array_slice($words, 1)), 0, 1, 'C');
        }

        $this->SetDrawColor(...$this->purple);
        $this->SetLineWidth(0.3);
        $this->Line(10, 32, $leftW - 10, 32);

        $this->SetTextColor(180, 160, 220);
        $this->SetFont('Helvetica', '', 5.5);
        $this->SetXY(3, 34);
        $this->MultiCell($leftW - 6, 4, "L'INNOVATION AU SERVICE\nDE DEMAIN", 0, 'C');

        $this->SetTextColor(150, 150, 180);
        $this->SetFont('Helvetica', 'I', 5);
        $this->SetXY(3, $H - 8);
        $this->Cell($leftW - 6, 4, 'Merci de votre participation !', 0, 0, 'C');

        // ── PANNEAU CENTRAL ───────────────────────────────────
        $this->SetFillColor(...$this->white);
        $this->Rect($leftW, 0, $midW, $H, 'F');

        $this->SetTextColor(...$this->dark);
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetXY($leftW + 4, 5);
        $this->Cell($midW - 8, 8, 'TICKET PARTICIPANT', 0, 1, 'C');

        $this->SetFont('Helvetica', '', 6);
        $this->SetTextColor(130, 130, 150);
        $this->SetXY($leftW + 4, 13);
        $this->Cell($midW - 8, 4, 'Acces valable pour une seule personne', 0, 1, 'C');

        $this->SetFillColor(...$this->purple);
        $this->Rect($leftW + $midW / 2 - 6, 18, 12, 0.8, 'F');

        $rows = [
            ['NOM DU PARTICIPANT', $nom],
            ['EVENEMENT',          $event],
            ['DATE',               $debut . ($fin && $fin !== $debut ? ' - ' . $fin : '')],
            ['LIEU',               $lieu],
        ];
        $y = 22;
        foreach ($rows as $row) {
            $this->drawInfoRow($leftW + 4, $y, $midW - 8, $row[0], $row[1]);
            $y += 16;
        }

        $this->SetFillColor(...$this->light);
        $this->RoundedRect($leftW + 4, $H - 18, $midW - 8, 14, 2, 'F');
        $this->SetTextColor(...$this->purple);
        $this->SetFont('Helvetica', 'B', 6);
        $this->SetXY($leftW + 8, $H - 16);
        $this->Cell(0, 4, 'INFORMATIONS', 0, 1);
        $this->SetTextColor(...$this->dark);
        $this->SetFont('Helvetica', '', 5.5);
        $this->SetXY($leftW + 8, $H - 12);
        $this->MultiCell($midW - 16, 3.5, "Presentez ce ticket a l'entree de l'evenement.\nCe ticket est personnel et non transferable.", 0);

        // Ligne pointillée
        $this->SetDrawColor(200, 200, 210);
        $this->SetLineWidth(0.2);
        $this->SetDash(2, 2);
        $this->Line($leftW + $midW, 4, $leftW + $midW, $H - 4);
        $this->SetDash();

        // ── PANNEAU DROIT ─────────────────────────────────────
        $rx = $leftW + $midW;
        $this->SetFillColor(...$this->white);
        $this->Rect($rx, 0, $rightW, $H, 'F');

        $this->SetFillColor(...$this->purple);
        $this->RoundedRect($rx + 4, 5, $rightW - 8, 7, 2, 'F');
        $this->SetTextColor(...$this->white);
        $this->SetFont('Helvetica', 'B', 7);
        $this->SetXY($rx + 4, 6.5);
        $this->Cell($rightW - 8, 4, 'VOTRE TICKET', 0, 0, 'C');

        // ── QR CODE UNIQUE PAR PARTICIPATION ──────────────────
        // Les données encodées : CODE|EVENT|NOM|HASH
        // → Scanner le QR révèle toutes les infos + hash de vérification
        $qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=M&data=' . urlencode($qrData);
        $qrPath = sys_get_temp_dir() . '/qr_pid' . $pid . '.png';

        if (!is_file($qrPath) || (time() - filemtime($qrPath)) > 3600) {
            $qrContent = @file_get_contents($qrUrl);
            if ($qrContent) {
                file_put_contents($qrPath, $qrContent);
            }
        }
        if (is_file($qrPath)) {
            $this->Image($qrPath, $rx + 7, 15, 34, 34);
        } else {
            $this->SetFillColor(230, 230, 240);
            $this->Rect($rx + 7, 15, 34, 34, 'F');
            $this->SetTextColor(150, 150, 170);
            $this->SetFont('Helvetica', '', 5.5);
            $this->SetXY($rx + 7, 30);
            $this->Cell(34, 4, 'QR Code', 0, 0, 'C');
        }

        // ID Participation
        $this->SetFillColor(...$this->purple);
        $this->RoundedRect($rx + 3, 52, $rightW - 6, 13, 2, 'F');
        $this->SetTextColor(200, 190, 230);
        $this->SetFont('Helvetica', '', 5.5);
        $this->SetXY($rx + 3, 53.5);
        $this->Cell($rightW - 6, 4, 'ID PARTICIPATION', 0, 0, 'C');
        $this->SetTextColor(...$this->white);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetXY($rx + 3, 58);
        $this->Cell($rightW - 6, 5, $code, 0, 0, 'C');

        // Code de sécurité
        $this->SetTextColor(...$this->purple);
        $this->SetFont('Helvetica', 'B', 6);
        $this->SetXY($rx + 3, 68);
        $this->Cell($rightW - 6, 4, 'CODE DE SECURITE', 0, 0, 'C');
        $this->SetTextColor(...$this->dark);
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetXY($rx + 3, 73);
        $this->Cell($rightW - 6, 4, $secFmt, 0, 0, 'C');

        $this->SetTextColor(130, 130, 150);
        $this->SetFont('Helvetica', 'I', 7);
        $this->SetXY($rx + 3, $H - 12);
        $this->Cell($rightW - 6, 4, 'Merci !', 0, 0, 'C');
        $this->SetFont('Helvetica', '', 5);
        $this->SetXY($rx + 3, $H - 8);
        $this->MultiCell($rightW - 6, 3, "Nous vous souhaitons\nun excellent evenement.", 0, 'C');
    }

    private function drawInfoRow(float $x, float $y, float $w, string $label, string $value): void
    {
        $this->SetFillColor(...$this->purple);
        $this->Circle($x + 3, $y + 4, 3, 'F');
        $this->SetTextColor(140, 140, 160);
        $this->SetFont('Helvetica', '', 5.5);
        $this->SetXY($x + 9, $y + 1);
        $this->Cell($w - 10, 4, strtoupper($label), 0);
        $this->SetTextColor(...$this->dark);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetXY($x + 9, $y + 6);
        $this->Cell($w - 10, 5, $value, 0);
    }

    private function Circle(float $x, float $y, float $r, string $style = ''): void
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    private function Ellipse(float $x, float $y, float $rx, float $ry, string $style = ''): void
    {
        $lx = 4/3*(M_SQRT2-1)*$rx; $ly = 4/3*(M_SQRT2-1)*$ry;
        $k = $this->k; $h = $this->h;
        $op = match($style){'F'=>'f','FD','DF'=>'B',default=>'S'};
        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$rx)*$k,($h-$y)*$k, ($x+$rx)*$k,($h-($y-$ly))*$k,($x+$lx)*$k,($h-($y-$ry))*$k,$x*$k,($h-($y-$ry))*$k,
            ($x-$lx)*$k,($h-($y-$ry))*$k,($x-$rx)*$k,($h-($y-$ly))*$k,($x-$rx)*$k,($h-$y)*$k,
            ($x-$rx)*$k,($h-($y+$ly))*$k,($x-$lx)*$k,($h-($y+$ry))*$k,$x*$k,($h-($y+$ry))*$k,
            ($x+$lx)*$k,($h-($y+$ry))*$k,($x+$rx)*$k,($h-($y+$ly))*$k,($x+$rx)*$k,($h-$y)*$k,
            $op));
    }

    private function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = ''): void
    {
        $k = 4/3*(M_SQRT2-1);
        $op = match($style){'F'=>'f','FD','DF'=>'B',default=>'S'};
        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F l %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F l %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F l %.2F %.2F %.2F %.2F %.2F %.2F c h %s',
            ($x+$r)*$this->k,($this->h-$y)*$this->k,
            ($x+$r+$k*$r)*$this->k,($this->h-$y)*$this->k,($x+$w)*$this->k,($this->h-($y+$r-$k*$r))*$this->k,($x+$w)*$this->k,($this->h-($y+$r))*$this->k,
            ($x+$w)*$this->k,($this->h-($y+$h-$r))*$this->k,
            ($x+$w)*$this->k,($this->h-($y+$h-$r+$k*$r))*$this->k,($x+$w-$r+$k*$r)*$this->k,($this->h-($y+$h))*$this->k,($x+$w-$r)*$this->k,($this->h-($y+$h))*$this->k,
            ($x+$r)*$this->k,($this->h-($y+$h))*$this->k,
            ($x+$r-$k*$r)*$this->k,($this->h-($y+$h))*$this->k,$x*$this->k,($this->h-($y+$h-$r+$k*$r))*$this->k,$x*$this->k,($this->h-($y+$h-$r))*$this->k,
            $x*$this->k,($this->h-($y+$r))*$this->k,
            $x*$this->k,($this->h-($y+$r-$k*$r))*$this->k,($x+$r-$k*$r)*$this->k,($this->h-$y)*$this->k,($x+$r)*$this->k,($this->h-$y)*$this->k,
            $op));
    }

    private function SetDash(float $black = 0, float $white = 0): void
    {
        $this->_out($black !== 0 ? sprintf('[%.3F %.3F] 0 d', $black*$this->k, $white*$this->k) : '[] 0 d');
    }

    private function clean(string $s): string
    {
        return strtr($s, ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a','ù'=>'u','û'=>'u','î'=>'i','ï'=>'i','ô'=>'o','ç'=>'c','É'=>'E','È'=>'E','Ê'=>'E','À'=>'A','Â'=>'A','Î'=>'I','Ô'=>'O','Ç'=>'C','Ù'=>'U']);
    }

    private function formatDate(string $d): string
    {
        if (!$d) return '';
        $ts = strtotime($d);
        if (!$ts) return $d;
        $m = ['Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sep','Oct','Nov','Dec'];
        return date('d', $ts) . ' ' . $m[(int)date('m', $ts)-1] . ' ' . date('Y', $ts);
    }
}