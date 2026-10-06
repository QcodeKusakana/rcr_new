<?php
/**
 * Carte de membre RCR (PDF, format carte 85,6 x 54 mm), téléchargeable / imprimable.
 * Accès : le membre connecté lui-même (ou un administrateur avec ?cod=ID).
 * Délivrée uniquement si la cotisation est à jour. Le QR renvoie vers la page publique de vérification.
 */
require_once __DIR__ . '/../functions/main_function.php';

require_once __DIR__ . '/../includes/rbac.php';
$estAdmin = isset($_SESSION['id_adm']) && has_permission($bdd, 'membres.voir');
$id = $estAdmin && isset($_GET['cod']) ? (int) $_GET['cod'] : (int) ($_SESSION['id_ad'] ?? 0);
if ($id <= 0) {
    http_response_code(403);
    die('Accès refusé. Veuillez vous connecter à votre espace membre.');
}

$q = $bdd->prepare('SELECT a.codes, a.nom, a.postnom, a.prenom, a.passeport, a.statut, a.date_echeance, a.dat_adhesion,
                           q.designation, g.nom_gd
                    FROM adhesion a
                    JOIN qualites q ON q.id_qt = a.id_qt
                    JOIN grades g ON g.id_gd = a.grade
                    WHERE a.id_ad = ?');
$q->execute([$id]);
$m = $q->fetch(PDO::FETCH_ASSOC);
if (!$m) { http_response_code(404); die('Membre introuvable.'); }

$aJour = !empty($m['date_echeance']) && $m['date_echeance'] >= date('Y-m-d') && $m['statut'] === 'actif';
if (!$aJour && !$estAdmin) {
    http_response_code(403);
    die('Votre carte sera disponible dès que votre cotisation sera à jour.');
}

require_once __DIR__ . '/../admin/pages/TCPDF-main/tcpdf.php';

$pdf = new TCPDF('L', 'mm', [54, 85.6], true, 'UTF-8', false);
$pdf->SetCreator('RCR');
$pdf->SetTitle('Carte de membre ' . $m['codes']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);

$ink = [27, 42, 68]; $gold = [156, 122, 46];
foreach (['recto', 'verso'] as $face) {
    $pdf->AddPage();
    if ($face === 'recto') {
        $pdf->SetFillColorArray($ink);   $pdf->Rect(0, 0, 85.6, 14, 'F');
        $pdf->SetFillColorArray($gold);  $pdf->Rect(0, 14, 85.6, 1.2, 'F');
        $logo = __DIR__ . '/../media/lo/logorcr.png';
        if (is_file($logo)) { $pdf->Image($logo, 3, 1.8, 10.4, 10.4, '', '', '', true); }
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 7); $pdf->SetXY(16, 3.2);   $pdf->Cell(67, 4, 'RASSEMBLEMENT DES CHRÉTIENS RÉPUBLICAINS', 0, 1, 'L', false, '', 1); // stretch=1 : jamais coupé
        $pdf->SetFont('helvetica', '', 6.5);  $pdf->SetXY(16, 7.6); $pdf->Cell(66, 3, 'CARTE DE MEMBRE', 0, 1);

        $photo = realpath(__DIR__ . '/../media/passeport/' . basename((string) $m['passeport']));
        if ($photo && is_file($photo)) { $pdf->Image($photo, 4, 18, 21, 27, '', '', '', true, 300, '', false, false, 1, 'CM'); }

        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY(28, 18); $pdf->MultiCell(54, 4, mb_strtoupper($m['nom'] . ' ' . $m['postnom']), 0, 'L');
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetX(28); $pdf->MultiCell(54, 4, $m['prenom'], 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(28, 29); $pdf->Cell(54, 3.6, 'Membre ' . $m['designation'] . ' - ' . $m['nom_gd'], 0, 1);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetXY(28, 34); $pdf->Cell(54, 4, 'N° ' . $m['codes'], 0, 1);
        $pdf->SetFont('helvetica', '', 6.5); $pdf->SetTextColor(90, 90, 90);
        $pdf->SetXY(28, 39); $pdf->Cell(54, 3, 'Valable jusqu\'au ' . ($m['date_echeance'] ? date('d/m/Y', strtotime($m['date_echeance'])) : '-'), 0, 1);
        $pdf->SetFillColorArray($gold); $pdf->Rect(0, 51.6, 85.6, 2.4, 'F');
    } else {
        $url = 'https://rcr.cd/index.php?pages=verifier&code=' . rawurlencode($m['codes']);
        $pdf->SetFillColorArray($ink); $pdf->Rect(0, 0, 85.6, 54, 'F');
        $pdf->write2DBarcode($url, 'QRCODE,M', 28, 6, 30, 30, ['border' => 0, 'padding' => 1.2, 'fgcolor' => [0, 0, 0], 'bgcolor' => [255, 255, 255]], 'N');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetXY(5, 38); $pdf->MultiCell(75.6, 3.4, "Scannez pour vérifier l'authenticité de cette carte\nrcr.cd/index.php?pages=verifier", 0, 'C');
        $pdf->SetTextColor(212, 175, 90);
        $pdf->SetXY(5, 47); $pdf->Cell(75.6, 3, 'Rassembler pour bâtir un pays plus beau qu\'avant', 0, 1, 'C');
    }
}
$pdf->Output('Carte_RCR_' . preg_replace('/[^A-Z0-9]/', '', $m['codes']) . '.pdf', 'I');
