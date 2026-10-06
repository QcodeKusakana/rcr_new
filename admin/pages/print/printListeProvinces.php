<?php
/**
 * Liste PDF des membres d'une province (?IdProv=n) — Administration → Statistiques / Provinces.
 *
 * Accès : session administrateur + permission « membres.voir » (script appelé directement par URL,
 * donc hors du point de passage admin/index.php). Requêtes préparées ; LEFT JOIN : un membre dont le
 * territoire ou le secteur est inconnu figure quand même dans la liste (l'ancien INNER JOIN l'omettait).
 * Présentation : A4 paysage, bandeau bleu encre / liseré or (identité RCR), tableau à lignes alternées,
 * en-tête de tableau répété à chaque page, pagination « Page x / y », pied de page piloté par la base.
 */
require_once '../../functions/main_function.php';
require_once __DIR__ . '/../../../includes/rbac.php';

if (!isset($_SESSION['id_adm']) || !has_permission($bdd, 'membres.voir')) {
    http_response_code(403);
    die('Accès refusé.');
}

require_once '../TCPDF-main/tcpdf.php';

const LP_INK  = [27, 42, 68];
const LP_GOLD = [156, 122, 46];
const LP_LOGO = __DIR__ . '/../../../media/lo/logorcr.png';

$idProv = (int) ($_GET['IdProv'] ?? 0);
$st = $bdd->prepare('SELECT * FROM provinces WHERE id_p = ?');
$st->execute([$idProv]);
$prov = $st->fetch(PDO::FETCH_ASSOC);
if (!$prov) {
    http_response_code(404);
    die('Province introuvable.');
}

$st = $bdd->prepare(
    "SELECT a.codes, a.nom, a.postnom, a.prenom, a.sexe, a.telephone, a.statut, t.nom_tr, s.nom_sec
       FROM adhesion a
  LEFT JOIN territoires t ON t.id_tr = a.territoire
  LEFT JOIN secteurs s ON s.id_sec = a.secteur
      WHERE a.province = ?
   ORDER BY t.nom_tr, s.nom_sec, a.nom, a.prenom"
);
$st->execute([$idProv]);
$membres = $st->fetchAll(PDO::FETCH_ASSOC);

$tel = function_exists('reglage') ? trim(reglage('contact_telephone') . '  ·  ' . reglage('contact_telephone2'), " ·") : '';
$pied = trim((function_exists('reglage') ? reglage('contact_adresse') : '') . ($tel !== '' ? '  |  ' . $tel : '') . (function_exists('reglage') && reglage('contact_email') !== '' ? '  |  ' . reglage('contact_email') : ''), ' |');
$titreProv = (string) $prov['nom_p'];

class ListePDF extends TCPDF
{
    public string $titreProv = '';
    public string $pied = '';

    public function Header()
    {
        $l = $this->getPageWidth();
        $this->SetFillColorArray(LP_INK);
        $this->Rect(0, 0, $l, 24, 'F');
        $this->SetFillColorArray(LP_GOLD);
        $this->Rect(0, 24, $l, 1.1, 'F');
        if (is_file(LP_LOGO)) {
            $this->Image(LP_LOGO, 12, 3.5, 17, 17);
        }
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 15);
        $this->SetXY(33, 5);
        $this->Cell(0, 7, 'RASSEMBLEMENT DES CHRÉTIENS RÉPUBLICAINS', 0, 1, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->SetX(33);
        $this->SetTextColor(235, 220, 170);
        $this->Cell(0, 5, 'Parti politique · Arrêté n° 010/2006 du 30 janvier 2006', 0, 0, 'L');
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY($l - 112, 8);
        $this->Cell(100, 8, 'Province : ' . $this->titreProv, 0, 0, 'R');
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetDrawColorArray(LP_GOLD);
        $this->Line(12, $this->GetY(), $this->getPageWidth() - 12, $this->GetY());
        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, $this->pied, 0, 0, 'L');
        $this->SetX(-60);
        $this->Cell(48, 5, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

$pdf = new ListePDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->titreProv = $titreProv;
$pdf->pied = $pied;
$pdf->SetCreator('RCR');
$pdf->SetAuthor('Rassemblement des Chrétiens Républicains');
$pdf->SetTitle('Liste des membres — ' . $titreProv);
$pdf->SetMargins(12, 30, 12);
$pdf->SetHeaderMargin(0);
$pdf->SetAutoPageBreak(true, 16);
$pdf->setFontSubsetting(true);
$pdf->AddPage();

// Titre + synthèse
$actifs = count(array_filter($membres, fn($m) => $m['statut'] === 'actif'));
$pdf->SetTextColorArray(LP_INK);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 8, 'Liste des membres de la province ' . $titreProv, 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9.5);
$pdf->SetTextColor(90, 90, 90);
$pdf->Cell(0, 5, 'Édité le ' . date('d/m/Y à H:i') . '  ·  ' . count($membres) . ' membre(s)  ·  ' . $actifs . ' actif(s)', 0, 1, 'L');
$pdf->Ln(3);

// Colonnes : [libellé, largeur, alignement]
$cols = [['N°', 10, 'C'], ['Code', 34, 'L'], ['Nom complet', 68, 'L'], ['Sexe', 12, 'C'], ['Téléphone', 36, 'L'], ['Territoire', 44, 'L'], ['Secteur', 40, 'L'], ['Statut', 22, 'C']];
$libStatut = ['actif' => 'Actif', 'en_attente' => 'En attente', 'expire' => 'Expiré', 'suspendu' => 'Suspendu'];

$entete = function () use ($pdf, $cols) {
    $pdf->SetFillColorArray(LP_INK);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 9);
    foreach ($cols as [$lib, $w, $al]) {
        $pdf->Cell($w, 8, $lib, 0, 0, $al, true);
    }
    $pdf->Ln();
};
$cut = fn($txt, $w, $taille) => mb_strimwidth((string) $txt, 0, (int) floor($w / ($taille * 0.19)), '…', 'UTF-8');

if (!$membres) {
    $pdf->Ln(6);
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, 'Aucun membre enregistré dans cette province pour le moment.', 0, 1, 'C');
} else {
    $entete();
    $pdf->SetDrawColor(225, 228, 235);
    $n = 0;
    foreach ($membres as $m) {
        $n++;
        // Saut de page anticipé : l'en-tête du tableau est répété
        if ($pdf->GetY() + 7 > $pdf->getPageHeight() - 18) {
            $pdf->AddPage();
            $entete();
            $pdf->SetDrawColor(225, 228, 235);
        }
        $pdf->SetFillColor($n % 2 ? 255 : 244, $n % 2 ? 255 : 246, $n % 2 ? 255 : 250);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->SetFont('helvetica', '', 9);
        $nom = trim($m['nom'] . ' ' . $m['postnom'] . ' ' . $m['prenom']);
        $vals = [
            (string) $n, (string) $m['codes'], $cut($nom, 68, 9), $m['sexe'] ?: '—', (string) $m['telephone'],
            $cut($m['nom_tr'] ?? '—', 44, 9), $cut($m['nom_sec'] ?? '—', 40, 9), $libStatut[$m['statut']] ?? $m['statut'],
        ];
        foreach ($cols as $i => [$lib, $w, $al]) {
            if ($lib === 'Statut') {
                $pdf->SetFont('helvetica', 'B', 8.5);
                $pdf->SetTextColor(...($m['statut'] === 'actif' ? [30, 120, 60] : ($m['statut'] === 'en_attente' ? [150, 100, 10] : [160, 40, 40])));
            }
            $pdf->Cell($w, 7, $vals[$i], 'B', 0, $al, true);
        }
        $pdf->Ln();
    }
}

$pdf->Output('membres_' . preg_replace('/[^A-Za-z0-9]+/', '_', $titreProv) . '.pdf', 'I');
