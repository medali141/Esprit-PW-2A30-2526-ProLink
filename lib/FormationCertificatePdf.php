<?php
declare(strict_types=1);

require_once __DIR__ . '/fpdf.php';

/**
 * Génère le PDF d'un certificat ProLink pour une ligne d'inscription
 * (telle que renvoyée par FormationP::getInscription()).
 *
 * Utilisé par :
 *  - view/BackOffice/formation/certificat.php   (délivrance manuelle par admin)
 *  - view/FrontOffice/formation_certificat.php  (auto-service après quiz réussi)
 */
class FormationCertificatePdf
{
    /**
     * Envoie le PDF au navigateur (inline) avec un nom de fichier déduit
     * de l'inscription. Termine la sortie via exit().
     *
     * @param array<string, mixed> $row jointure inscription + formation
     */
    public static function render(array $row): void
    {
        $enc = static function (?string $s): string {
            $s = (string) ($s ?? '');
            if ($s === '') return '';
            $out = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);
            return $out === false ? $s : $out;
        };
        $fmtDate = static function (?string $d): string {
            if (!$d) return '';
            $t = strtotime((string) $d);
            return $t ? date('d/m/Y', $t) : (string) $d;
        };

        $nom = trim((string) ($row['prenom'] ?? '') . ' ' . (string) ($row['nom'] ?? ''));
        if ($nom === '') $nom = (string) ($row['nom'] ?? 'Participant');
        $titreForm  = (string) ($row['formation_titre'] ?? '');
        $categorie  = (string) ($row['formation_categorie'] ?? '');
        $dateDebut  = $fmtDate($row['formation_date_debut'] ?? null);
        $dateFin    = $fmtDate($row['formation_date_fin'] ?? null);
        $dateEmis   = date('d/m/Y');
        $certNumero = 'PL-' . str_pad((string) (int) ($row['id_inscription'] ?? 0), 6, '0', STR_PAD_LEFT);

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $pdf->SetFillColor(247, 251, 255);
        $pdf->Rect(0, 0, 297, 210, 'F');

        $pdf->SetDrawColor(0, 115, 177);
        $pdf->SetLineWidth(2.2);
        $pdf->Rect(10, 10, 277, 190);
        $pdf->SetDrawColor(6, 182, 212);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(14, 14, 269, 182);

        $pdf->SetFillColor(0, 115, 177);
        $pdf->Rect(110, 24, 77, 14, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetXY(110, 24);
        $pdf->Cell(77, 14, $enc('ProLink Academy'), 0, 0, 'C');

        $pdf->SetTextColor(7, 59, 76);
        $pdf->SetFont('Helvetica', 'B', 30);
        $pdf->SetXY(0, 50);
        $pdf->Cell(297, 14, $enc('CERTIFICAT'), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 16);
        $pdf->SetTextColor(91, 107, 114);
        $pdf->Cell(297, 8, $enc('de participation à la formation'), 0, 1, 'C');

        $pdf->SetTextColor(7, 59, 76);
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->Ln(8);
        $pdf->Cell(297, 6, $enc('Le présent certificat est délivré à'), 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', 'B', 30);
        $pdf->SetTextColor(0, 115, 177);
        $pdf->Cell(297, 14, $enc($nom), 0, 1, 'C');

        $pdf->SetDrawColor(6, 182, 212);
        $pdf->SetLineWidth(0.6);
        $nameWidth = max(120.0, min(220.0, (float) $pdf->GetStringWidth($enc($nom)) + 40));
        $centerX = 297 / 2;
        $y = $pdf->GetY() - 2;
        $pdf->Line($centerX - $nameWidth / 2, $y, $centerX + $nameWidth / 2, $y);

        $pdf->SetTextColor(91, 107, 114);
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->Ln(4);
        $pdf->Cell(297, 6, $enc('pour avoir suivi avec succès la formation :'), 0, 1, 'C');

        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetTextColor(7, 59, 76);
        $pdf->Ln(2);
        $pdf->Cell(297, 12, $enc('"' . $titreForm . '"'), 0, 1, 'C');

        if ($categorie !== '') {
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->SetTextColor(14, 116, 144);
            $catLabel = $enc($categorie);
            $w = (float) $pdf->GetStringWidth($catLabel) + 14;
            $pdf->SetFillColor(207, 250, 254);
            $pdf->SetDrawColor(165, 243, 252);
            $pdf->SetLineWidth(0.3);
            $pdf->SetXY((297 - $w) / 2, $pdf->GetY() + 2);
            $pdf->Cell($w, 8, $catLabel, 1, 1, 'C', true);
        }

        if ($dateDebut !== '' || $dateFin !== '') {
            $period = $dateDebut !== '' && $dateFin !== ''
                ? 'du ' . $dateDebut . ' au ' . $dateFin
                : ($dateDebut !== '' ? 'à partir du ' . $dateDebut : 'jusqu\'au ' . $dateFin);
            $pdf->SetFont('Helvetica', 'I', 12);
            $pdf->SetTextColor(91, 107, 114);
            $pdf->Ln(4);
            $pdf->Cell(297, 6, $enc($period), 0, 1, 'C');
        }

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(91, 107, 114);
        $pdf->SetXY(30, 170);
        $pdf->Cell(110, 5, $enc('Délivré le : ' . $dateEmis), 0, 1, 'L');
        $pdf->SetXY(30, 176);
        $pdf->Cell(110, 5, $enc('N° certificat : ' . $certNumero), 0, 0, 'L');

        $pdf->SetDrawColor(7, 59, 76);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(190, 175, 270, 175);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(7, 59, 76);
        $pdf->SetXY(190, 177);
        $pdf->Cell(80, 5, $enc('Signature & cachet — ProLink'), 0, 0, 'C');

        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $nom);
        $filename = 'certificat_' . $certNumero . '_' . ($safeName !== '' ? $safeName : 'participant') . '.pdf';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $pdf->Output('I', $filename);
        exit;
    }
}
