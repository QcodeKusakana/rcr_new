<?php
/**
 * Reçu PDF d'un paiement CONFIRMÉ (statut paid).
 *   member/recu.php?t=p&id=12   -> adhésion / cotisation (table payments)
 *   member/recu.php?t=d&id=3    -> don (table dons)
 * Accès : le membre propriétaire (session) ou un administrateur ayant la permission paiements.voir.
 * Aucun reçu n'est délivré tant que FlexPay n'a pas confirmé le paiement côté serveur.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/rbac.php';

$type = ($_GET['t'] ?? 'p') === 'd' ? 'd' : 'p';
$id   = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Demande invalide.'); }

if ($type === 'p') {
    $s = $bdd->prepare("SELECT p.id, p.reference, p.montant, p.devise, p.canal, p.provider_reference, p.type_transaction, p.created_at,
                               p.periode_debut, p.periode_fin, p.id_ad, a.codes, a.nom, a.postnom, a.prenom, q.designation, g.nom_gd
                        FROM payments p
                        LEFT JOIN adhesion a ON a.id_ad = p.id_ad
                        LEFT JOIN qualites q ON q.id_qt = a.id_qt
                        LEFT JOIN grades g ON g.id_gd = a.grade
                        WHERE p.id = ? AND p.status = 'paid'");
} else {
    $s = $bdd->prepare("SELECT d.id_don AS id, d.reference, d.montant, d.devise, d.canal, d.provider_reference, d.type_don, d.frequence, d.created_at,
                               d.id_ad, d.nom_donateur AS nom, d.postnom, d.prenom
                        FROM dons d WHERE d.id_don = ? AND d.status = 'paid'");
}
$s->execute([$id]);
$r = $s->fetch(PDO::FETCH_ASSOC);
if (!$r) { http_response_code(404); exit('Reçu indisponible : paiement non confirmé ou introuvable.'); }

$estProprio = !empty($_SESSION['id_ad']) && (int) $_SESSION['id_ad'] === (int) ($r['id_ad'] ?? 0);
$estAdmin   = !empty($_SESSION['id_adm']) && has_permission($bdd, 'paiements.voir');
if (!$estProprio && !$estAdmin) { http_response_code(403); exit('Accès refusé.'); }

require_once __DIR__ . '/../admin/pages/TCPDF-main/tcpdf.php';
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->SetCreator('RCR'); $pdf->SetTitle('Reçu ' . $r['reference']);
$pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
$pdf->SetMargins(14, 14, 14); $pdf->SetAutoPageBreak(true, 14);
$pdf->AddPage();

$logo = realpath(__DIR__ . '/../media/lo/logorcr.png') ?: realpath(__DIR__ . '/../media/lo/logo.png');
if ($logo) { $pdf->Image($logo, 14, 12, 22); }
$pdf->SetFont('helvetica', 'B', 12); $pdf->SetTextColor(27, 42, 68);
$pdf->SetXY(40, 14); $pdf->Cell(0, 7, 'Rassemblement des Chrétiens Républicains', 0, 1, 'L', false, '', 1);
$pdf->SetFont('helvetica', '', 9); $pdf->SetTextColor(90, 90, 90);
$pdf->SetX(40); $pdf->Cell(0, 5, 'rcr.cd', 0, 1);

$pdf->Ln(14);
$pdf->SetFont('helvetica', 'B', 13); $pdf->SetTextColor(156, 122, 46);
$pdf->Cell(0, 8, $type === 'd' ? 'REÇU DE DON' : 'REÇU DE PAIEMENT', 0, 1);
$pdf->SetTextColor(0, 0, 0); $pdf->SetFont('helvetica', '', 10);

$nom = trim(($r['nom'] ?? '') . ' ' . ($r['postnom'] ?? '') . ' ' . ($r['prenom'] ?? ''));
$lignes = [
    ['Reçu n°', $r['reference']],
    ['Date', date('d/m/Y H:i', strtotime((string) $r['created_at']))],
    ['Reçu de', $nom !== '' ? $nom : '—'],
];
if ($type === 'p') {
    $lignes[] = ['Identifiant membre', $r['codes'] ?? '—'];
    $lignes[] = ['Objet', ($r['type_transaction'] === 'cotisation' ? 'Cotisation' : 'Adhésion') . ' — ' . ($r['designation'] ?? '') . ' / ' . ($r['nom_gd'] ?? '')];
    if (!empty($r['periode_debut'])) {
        $lignes[] = ['Période couverte', date('d/m/Y', strtotime($r['periode_debut'])) . ' au ' . date('d/m/Y', strtotime($r['periode_fin']))];
    }
} else {
    $lignes[] = ['Objet', 'Don ' . ($r['type_don'] === 'regulier' ? 'régulier (' . $r['frequence'] . ')' : 'ponctuel')];
}
$lignes[] = ['Moyen de paiement', $r['canal'] === 'carte' ? 'Carte bancaire' : 'Mobile Money'];
if (!empty($r['provider_reference'])) { $lignes[] = ['Réf. opérateur', $r['provider_reference']]; }

foreach ($lignes as [$k, $v]) {
    $pdf->SetFont('helvetica', 'B', 10); $pdf->Cell(42, 7, $k, 'B', 0);
    $pdf->SetFont('helvetica', '', 10);  $pdf->MultiCell(0, 7, (string) $v, 'B', 'L');
}
$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 14); $pdf->SetTextColor(27, 42, 68);
$pdf->Cell(0, 10, 'Montant payé : ' . number_format((float) $r['montant'], 2, ',', ' ') . ' ' . $r['devise'], 0, 1);
$pdf->SetFont('helvetica', 'I', 8); $pdf->SetTextColor(110, 110, 110);
$pdf->Ln(6);
$pdf->MultiCell(0, 4, 'Ce reçu est généré automatiquement après confirmation du paiement par le prestataire. Il ne constitue pas un justificatif fiscal.', 0, 'L');

$pdf->Output('recu_' . preg_replace('/[^A-Za-z0-9_-]/', '', $r['reference']) . '.pdf', 'I');
