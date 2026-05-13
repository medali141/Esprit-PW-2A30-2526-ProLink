<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../controller/AuthController.php';
$__u = (new AuthController())->profile();
if (!$__u || strtolower($__u['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/eventC.php';
require_once __DIR__ . '/../../../lib/fpdf.php';

$allowed = ['id_event', 'titre_event', 'description_event', 'type_event', 'date_debut', 'date_fin', 'lieu_event', 'capacite_max', 'statut', 'created_at'];
$sort = (string) ($_GET['sort'] ?? 'id_event');
$dir = (string) ($_GET['dir'] ?? 'asc');
if (!in_array($sort, $allowed, true)) {
    $sort = 'id_event';
}
$dir = strtoupper($dir) === 'DESC' ? 'desc' : 'asc';
$ec = new EventC();
$rows = $ec->listeEvent($sort, $dir);

function prolink_pdf_s(?string $s): string
{
    $s = (string) $s;
    if (function_exists('mb_convert_encoding')) {
        $t = @mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
        if (is_string($t) && $t !== '') {
            return $t;
        }
    }
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s);
        if (is_string($t) && $t !== false) {
            return $t;
        }
    }
    return $s;
}

function prolink_pdf_trim(string $s, int $maxChars = 40): string
{
    if (function_exists('mb_strimwidth')) {
        $s = mb_strimwidth($s, 0, $maxChars, '...', 'UTF-8');
    } elseif (strlen($s) > $maxChars) {
        $s = substr($s, 0, $maxChars - 3) . '...';
    }
    return prolink_pdf_s($s);
}

$COL = [
    'hero'  => [22, 50, 100],
    'hdr'   => [30, 58, 120],
    'line'  => [66, 120, 210],
    'rowA'  => [255, 255, 255],
    'rowB'  => [241, 245, 250],
    'ink'   => [35, 35, 45],
    'muted' => [95, 105, 120],
    'boxBg' => [244, 247, 252],
    'grd'   => [200, 210, 220],
];

class ProlinkEventsPDF extends FPDF
{
    public function __construct()
    {
        parent::__construct('L', 'mm', 'A4');
        $this->SetMargins(12, 10, 12);
        $this->SetAutoPageBreak(true, 18);
        $this->AliasNbPages();
    }

    public function Footer(): void
    {
        $this->SetY(-14);
        $this->SetDrawColor(200, 205, 220);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), 285, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(95, 105, 120);
        $this->SetX(12);
        $w = 273;
        $this->Cell($w * 0.6, 4, prolink_pdf_s('ProLink  |  Back-office  |  Export des evenements'), 0, 0, 'L');
        $this->Cell($w * 0.4, 4, prolink_pdf_s('Page ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }
}

$pdf = new ProlinkEventsPDF();
$w = [8, 38, 115, 20, 20, 20, 25, 8, 19];
$hRow = 7.0;
$hHead = 8.0;
$labelsPdf = ['Id', 'Titre', 'Description', 'Type', 'Debut', 'Fin', 'Lieu', 'Cap', 'Statut'];

$drawHero = static function (ProlinkEventsPDF $pdf) use ($COL) {
    $W = 297.0;
    $pdf->SetY(0);
    $pdf->SetX(0);
    $pdf->SetFillColor($COL['hero'][0], $COL['hero'][1], $COL['hero'][2]);
    $pdf->Rect(0, 0, $W, 28, 'F');
    $pdf->SetDrawColor($COL['line'][0], $COL['line'][1], $COL['line'][2]);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(0, 28, $W, 28);
    $pdf->SetXY(14, 7);
    $pdf->SetTextColor(200, 225, 255);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(0, 3, prolink_pdf_s('RAPPORT'), 0, 1, 'L');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetX(14);
    $pdf->Cell(0, 7, prolink_pdf_s('Liste des evenements'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(220, 230, 250);
    $pdf->SetX(14);
    $pdf->Cell(0, 4, prolink_pdf_s('Genere le ' . date('d/m/Y') . ' a ' . date('H:i')), 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY(32);
};

$drawInfoBar = static function (ProlinkEventsPDF $pdf, string $sort, string $dir, int $n) use ($COL) {
    $x = 12;
    $y = $pdf->GetY() + 2;
    $pdf->SetY($y);
    $ph = 7;
    $pw = 265;
    $pdf->SetDrawColor($COL['grd'][0], $COL['grd'][1], $COL['grd'][2]);
    $pdf->SetFillColor($COL['boxBg'][0], $COL['boxBg'][1], $COL['boxBg'][2]);
    $pdf->SetTextColor($COL['muted'][0], $COL['muted'][1], $COL['muted'][2]);
    $pdf->SetLineWidth(0.2);
    $pdf->Rect($x, $y, $pw, $ph, 'DF');
    $pdf->SetXY($x + 3, $y + 1.2);
    $pdf->SetFont('Arial', '', 7);
    $txt = prolink_pdf_s("Tri: {$sort}  " . strtoupper($dir) . "  |  {$n} evenement(s)");
    $pdf->Cell($pw - 6, 5, $txt, 0, 0, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY($y + $ph + 2);
};

$drawTableHead = static function (ProlinkEventsPDF $pdf, array $w, array $labelsPdf, int $hHead) use ($COL) {
    if ($pdf->GetX() < 1) {
        $pdf->SetX(12);
    }
    $pdf->SetDrawColor(25, 45, 90);
    $pdf->SetFillColor($COL['hdr'][0], $COL['hdr'][1], $COL['hdr'][2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $n = min(count($w), count($labelsPdf));
    for ($i = 0; $i < $n; $i++) {
        $pdf->Cell($w[$i], $hHead, prolink_pdf_s($labelsPdf[$i]), 1, 0, 'C', true);
    }
    $pdf->Ln();
    $pdf->SetTextColor(0, 0, 0);
};

$rows = is_array($rows) ? $rows : [];
$rowCount = count($rows);

$pdf->AddPage();
$drawHero($pdf);
$drawInfoBar($pdf, $sort, $dir, $rowCount);
$pdf->SetFont('Arial', '', 7);
$drawTableHead($pdf, $w, $labelsPdf, $hHead);
$n = 0;
foreach ($rows as $r) {
    if ($pdf->GetY() + $hRow > 194) {
        $pdf->AddPage();
        $drawTableHead($pdf, $w, $labelsPdf, $hHead);
        $pdf->SetFont('Arial', '', 7);
    }
    $n++;
    if ($n % 2 === 0) {
        $pdf->SetFillColor(241, 245, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->SetTextColor(35, 40, 55);
    $pdf->SetDrawColor(210, 218, 230);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell($w[0], $hRow, (string) ($r['id_event'] ?? ''), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($w[1], $hRow, prolink_pdf_trim((string) ($r['titre_event'] ?? ''), 32), 1, 0, 'L', true);
    $pdf->Cell($w[2], $hRow, prolink_pdf_trim((string) ($r['description_event'] ?? ''), 62), 1, 0, 'L', true);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell($w[3], $hRow, prolink_pdf_trim((string) ($r['type_event'] ?? ''), 18), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($w[4], $hRow, (string) ($r['date_debut'] ?? ''), 1, 0, 'C', true);
    $pdf->Cell($w[5], $hRow, (string) ($r['date_fin'] ?? ''), 1, 0, 'C', true);
    $pdf->Cell($w[6], $hRow, prolink_pdf_trim((string) ($r['lieu_event'] ?? ''), 16), 1, 0, 'L', true);
    $pdf->Cell($w[7], $hRow, (string) ($r['capacite_max'] ?? ''), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell($w[8], $hRow, prolink_pdf_trim((string) ($r['statut'] ?? ''), 14), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->Output('D', 'prolink_evenements_' . date('Y-m-d_His') . '.pdf');
exit;
