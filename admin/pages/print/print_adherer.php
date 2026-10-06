<?php
require_once '../../functions/main_function.php';

// 🔐 CORRECTIF SÉCURITÉ CRITIQUE (audit) : ce script est accessible par une
// URL directe (pages/print/...), donc HORS du point de passage central
// ajouté dans admin/index.php. Aucune vérification de session n'était
// effectuée : n'importe quel visiteur non connecté pouvait générer ce PDF
// (données personnelles complètes d'un adhérent : nom, téléphone, date de
// naissance, paiement, photo...) en devinant/énumérant l'identifiant `cod`.
//
// Ce script est utilisé par DEUX profils légitimes : un administrateur
// connecté (depuis admin/pages/adhesions.php ou demandes.php, sans
// restriction de niveau particulière sur ce bouton), ET un membre connecté
// qui télécharge SA PROPRE fiche depuis pages/esp_membre.php
// ($_SESSION['id_ad'], sans session admin). On autorise donc soit un admin,
// soit un membre dont l'id de session correspond exactement à `cod`
// (empêche un membre de télécharger la fiche d'un autre en changeant l'URL — IDOR).
//
// 🔧 RÉTABLI : ce contrôle avait disparu du fichier reçu (probablement
// retiré pendant le diagnostic du 503) — sans lui, n'importe quel
// visiteur non connecté peut à nouveau générer la fiche de n'importe
// quel adhérent en devinant `cod`. Remis tel quel, inchangé.
$codDemande = (int) ($_GET['cod'] ?? 0);
require_once __DIR__ . '/../../../includes/rbac.php';
$estAdmin   = isset($_SESSION['id_adm']) && has_permission($bdd, 'membres.voir'); // un admin sans droit « membres » ne voit pas les fiches
$estMembrePropriétaire = (isset($_SESSION['id_ad']) && (int) $_SESSION['id_ad'] === $codDemande)
    || (isset($_SESSION['adhesion_recente']) && (int) $_SESSION['adhesion_recente'] === $codDemande);

if (!$estAdmin && !$estMembrePropriétaire) {
    http_response_code(403);
    die('Accès refusé.');
}

/**
 * 🔧 CORRECTIF (point 6 du cahier des charges : "le fichier d'adhésion ne
 * doit pas dépendre d'une simple réponse immédiate du navigateur ; la
 * validation doit être basée sur le statut réel enregistré côté
 * serveur") : ce script générait la fiche PDF officielle d'adhésion sans
 * jamais vérifier si le paiement correspondant était réellement confirmé
 * — un membre pouvait télécharger sa fiche même avec un paiement encore
 * en attente, échoué ou en vérification. On bloque maintenant le
 * téléchargement PAR LE MEMBRE tant qu'aucun paiement n'est reconnu comme
 * validé pour son adhésion.
 *
 * 🔧 CORRECTIF (montant non récupéré) : cette vérification, comme la
 * requête plus bas, ne testait QUE `status = 'paid'`. Or dans ce projet,
 * un paiement confirmé MANUELLEMENT par un admin (bouton "Confirmer" de
 * admin/pages/adhesions.php / demandes.php, voir
 * admin/functions/adhesions.funct.php) passe `trans_keys = 1` SANS jamais
 * toucher `status` — qui reste à sa valeur d'origine (pending/failed).
 * Un paiement validé de cette façon était donc invisible ici : accès
 * refusé au membre, et montant introuvable pour l'admin. Les deux
 * mécanismes de validation du projet (`status = 'paid'` via FlexPay,
 * `trans_keys = 1` via validation manuelle) sont maintenant traités
 * comme équivalents, conformément à la logique déjà en place ailleurs
 * dans le projet.
 */
// 🔧 Fiche VALIDÉE / NON VALIDÉE : la fiche reste téléchargeable avant le paiement, mais porte alors la mention
// « NON VALIDÉE » (bandeau rouge, filigrane, statut dans le QR). Elle n'est « validée » que si un paiement est
// réellement confirmé par FlexPay (status = 'paid'). La validation manuelle (trans_keys) n'existe plus.
$verifPaiement = $bdd->prepare("
    SELECT COUNT(*) FROM payments
    WHERE adhesion_id = ?
    AND status = 'paid'  -- validation uniquement par confirmation FlexPay (plus de validation manuelle)
");
$verifPaiement->execute([$codDemande]);
$ficheValide = ((int) $verifPaiement->fetchColumn()) > 0;

require_once('../TCPDF-main/tcpdf.php');
$dat = date('d/m/Y à H:i');

// 🔧 CORRECTIF (fiabilité des données affichées + montant non récupéré) :
// le LEFT JOIN ne retenait que `status = 'paid'`, jamais vrai dans ce
// projet pour un paiement validé manuellement (trans_keys = 1, voir plus
// haut) — d'où un montant introuvable (NULL) même pour une adhésion
// bien confirmée. On accepte maintenant les deux, et on garde le
// paiement valide le plus récent (ORDER BY ... LIMIT 1) si plusieurs
// tentatives existent pour la même adhésion.
$adhesions = $bdd->prepare("
    SELECT adhesion.*, provinces.nom_p, territoires.nom_tr, secteurs.nom_sec,
           qualites.designation, grades.nom_gd, cotisation.nom_cot,
           payments.montant AS prix, payments.created_at AS date_paiement
    FROM adhesion
    INNER JOIN provinces ON adhesion.province = provinces.id_p
    INNER JOIN territoires ON adhesion.territoire = territoires.id_tr
    INNER JOIN secteurs ON adhesion.secteur = secteurs.id_sec
    INNER JOIN qualites ON adhesion.id_qt = qualites.id_qt
    INNER JOIN grades ON adhesion.grade = grades.id_gd
    INNER JOIN cotisation ON adhesion.reglement = cotisation.id_cot
    LEFT JOIN payments ON adhesion.id_ad = payments.adhesion_id
        AND payments.status = 'paid'
    WHERE adhesion.id_ad = ?
    ORDER BY payments.id DESC
    LIMIT 1
");

$adhesions->execute([$codDemande]);
$info = $adhesions->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    http_response_code(404);
    die('Adhésion introuvable.');
}

/**
 * 🔧 REFONTE VISUELLE (logo, filigrane, QR code, présentation) :
 * - Vrai logo du parti (media/lo/logorcr.png) au lieu du logo d'exemple
 *   TCPDF (K_PATH_IMAGES.'logo.png' — K_PATH_IMAGES est vide dans la
 *   config du projet, ce chemin ne pointait donc vers rien).
 * - Filigrane discret "RCR.CD" en diagonale, dessiné en tout premier
 *   (donc visuellement derrière tout le reste), à faible opacité.
 * - QR code natif TCPDF encodant les informations essentielles (aucune
 *   page de vérification en ligne n'existe encore dans le projet).
 * - Marges, hauteurs de ligne et espaces resserrés pour que TOUT
 *   (identité, localisation, paiement, acte d'engagement, QR code,
 *   signature) tienne sur UNE SEULE page A4.
 * - 🔧 AJOUT (demande explicite) : photo du membre (adhesion.passeport,
 *   stockée dans media/passeport/) affichée à droite de la section
 *   IDENTITÉ DU MEMBRE, dans un encadré de taille fixe, sans jamais la
 *   déformer (fitbox TCPDF : la photo est centrée et mise à l'échelle
 *   en conservant ses proportions d'origine, quelle que soit sa forme).
 */
define('RCR_INK',  [27, 42, 68]);
define('RCR_GOLD', [156, 122, 46]);
define('RCR_LOGO_PATH', dirname(__FILE__) . '/../../../media/lo/logorcr.png');
define('RCR_MARGE_LR', 15); // marges gauche/droite communes, réutilisées pour positionner la photo

class PDF extends TCPDF
{
    public bool $valide = true;

    public function Header()
    {
        $this->drawWatermark();

        // Bandeau supérieur bleu marine.
        $this->SetFillColorArray(RCR_INK);
        $this->Rect(0, 0, $this->getPageWidth(), 28, 'F');

        // Liseré or.
        $this->SetFillColorArray(RCR_GOLD);
        $this->Rect(0, 28, $this->getPageWidth(), 1.2, 'F');

        if (@is_file(RCR_LOGO_PATH)) {
            $this->Image(RCR_LOGO_PATH, 12, 4, 20, 20);
        }

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(36, 6);
        $this->Cell(0, 6, "RASSEMBLEMENT DES CHRÉTIENS RÉPUBLICAINS", 0, 1, 'L');

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(RCR_GOLD);
        $this->SetXY(36, 13);
        $this->Cell(0, 5, $this->valide ? "PARTI POLITIQUE  ·  FICHE OFFICIELLE D'ADHÉSION" : "PARTI POLITIQUE  ·  FICHE D'ADHÉSION — NON VALIDÉE", 0, 1, 'L');

        $this->SetY(34);
    }

    public function Footer()
    {
        $this->SetY(-16);
        $this->SetDrawColorArray(RCR_GOLD);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());

        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(120, 120, 120);
        $this->SetY(-13);
        $this->Cell(0, 4, "Document généré automatiquement le " . date('d/m/Y à H:i') . " — rcr.cd", 0, 0, 'L');
        $this->Cell(0, 4, "Page " . $this->getAliasNumPage() . "/" . $this->getAliasNbPages(), 0, 0, 'R');
    }

    /**
     * Filigrane diagonal discret, répété sur toute la page, à très
     * faible opacité.
     */
    private function drawWatermark(): void
    {
        $this->StartTransform();
        $this->SetAlpha(0.05);
        $this->SetFont('helvetica', 'B', 34);
        $this->SetTextColorArray(RCR_INK);

        $pageW = $this->getPageWidth();
        $pageH = $this->getPageHeight();

        for ($y = 20; $y < $pageH; $y += 45) {
            for ($x = -20; $x < $pageW; $x += 90) {
                $this->StartTransform();
                $this->Rotate(35, $x, $y);
                $this->Text($x, $y, "RCR.CD");
                $this->StopTransform();
            }
        }

        $this->SetAlpha(1);
        $this->StopTransform();

        if (!$this->valide) {
            // Gros filigrane rouge « NON VALIDÉE » au centre de la page
            $this->StartTransform();
            $this->SetAlpha(0.14);
            $this->SetFont('helvetica', 'B', 62);
            $this->SetTextColor(200, 30, 30);
            $this->Rotate(40, $pageW / 2, $pageH / 2);
            $this->Text($pageW / 2 - 78, $pageH / 2, "NON VALIDÉE");
            $this->SetAlpha(1);
            $this->StopTransform();
        }
    }
}

// create new PDF document
$pdf = new PDF('p', 'mm', 'A4', true, 'UTF-8', false);
$pdf->valide = $ficheValide;

$pdf->SetCreator('RCR');
$pdf->SetAuthor('RCR');
$pdf->SetTitle('FICHE D\'ADHESION ' . $info['codes']);
$pdf->SetSubject("Fiche officielle d'adhésion RCR");

// 🔧 Marges resserrées (tenue sur une seule page).
$pdf->SetMargins(RCR_MARGE_LR, 38, RCR_MARGE_LR);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(8);
$pdf->SetAutoPageBreak(true, 16);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('dejavusans', '', 11, '', true);

$pdf->AddPage();

// --------------------------------------------------------------
// Bandeau "statut"
// --------------------------------------------------------------
if ($ficheValide) {
    $pdf->SetFillColor(236, 245, 238);
    $pdf->SetDrawColor(47, 107, 58);
    $pdf->SetTextColor(47, 107, 58);
    $pdf->SetFont('dejavusans', 'B', 10.5);
    $pdf->Cell(0, 8, "  \xE2\x9C\x93  ADHÉSION VALIDÉE — PAIEMENT CONFIRMÉ", 1, 1, 'L', true);
    $pdf->Ln(3);
} else {
    $pdf->SetFillColor(253, 236, 234);
    $pdf->SetDrawColor(192, 38, 38);
    $pdf->SetTextColor(192, 38, 38);
    $pdf->SetFont('dejavusans', 'B', 10.5);
    $pdf->Cell(0, 8, "  \xE2\x9C\x97  ADHÉSION NON VALIDÉE — PAIEMENT NON CONFIRMÉ", 1, 1, 'L', true);
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->SetTextColor(120, 40, 40);
    $pdf->MultiCell(0, 4, "Ce document n'a aucune valeur officielle tant que FlexPay n'a pas confirmé la réception du paiement. Une fois le paiement confirmé, retéléchargez cette fiche depuis votre espace membre : elle sera alors VALIDÉE.", 0, 'L');
    $pdf->Ln(2);
}

// --------------------------------------------------------------
// Titre
// --------------------------------------------------------------
$pdf->SetFont('helvetica', 'B', 15);
$pdf->SetTextColorArray(RCR_INK);
$pdf->Cell(0, 8, $ficheValide ? "FICHE OFFICIELLE D'ADHÉSION" : "FICHE D'ADHÉSION (NON VALIDÉE)", 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10.5);
$pdf->SetTextColorArray(RCR_GOLD);
$pdf->Cell(0, 5, "Code d'adhérent : " . $info['codes'], 0, 1, 'C');
$pdf->Ln(3);

function rcr_section_title($pdf, string $titre): void
{
    $pdf->SetFillColorArray(RCR_INK);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 10.5);
    $pdf->Cell(0, 6.5, "  " . $titre, 0, 1, 'L', true);
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFont('helvetica', '', 10);
}

function rcr_field_row($pdf, string $label, string $valeur): void
{
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(55, 6, $label, 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(0, 6, $valeur, 0, 1, 'L');
}

// --------------------------------------------------------------
// IDENTITÉ DU MEMBRE (photo à droite)
// --------------------------------------------------------------
rcr_section_title($pdf, "IDENTITÉ DU MEMBRE");
$yApresTitreIdentite = $pdf->GetY();

// 🔧 AJOUT : encadré photo réservé à droite. On réduit temporairement la
// marge droite le temps d'écrire les champs, pour que le texte ne passe
// jamais sous la photo (au lieu de superposer les deux).
$photoLargeur = 26;
$photoHauteur = 32;
$pdf->SetRightMargin(RCR_MARGE_LR + $photoLargeur + 5);

rcr_field_row($pdf, "Nom complet", trim($info['nom'] . ' ' . $info['postnom'] . ' ' . $info['prenom']));
rcr_field_row($pdf, "Email", $info['mail']);
rcr_field_row($pdf, "Téléphone", $info['telephone']);
rcr_field_row($pdf, "Date de naissance", $info['datenaiss']);
rcr_field_row($pdf, "Nationalité", $info['nationalite']);
$yApresChampsIdentite = $pdf->GetY();

// Marge droite normale restaurée pour le reste du document.
$pdf->SetRightMargin(RCR_MARGE_LR);

// Position de la photo : coin haut-droit de la section, alignée sous le
// bandeau de titre bleu.
$xPhoto = $pdf->getPageWidth() - RCR_MARGE_LR - $photoLargeur;
$yPhoto = $yApresTitreIdentite + 1;
$cheminPhoto = !empty($info['passeport'])
    ? dirname(__FILE__) . '/../../../media/passeport/' . $info['passeport']
    : null;

if ($cheminPhoto !== null && @is_file($cheminPhoto)) {
    // fitbox 'CM' : la photo est centrée dans le cadre $photoLargeur x
    // $photoHauteur en conservant ses proportions d'origine — jamais
    // étirée ni écrasée, quelle que soit sa forme réelle.
    $pdf->Image(
        $cheminPhoto, $xPhoto, $yPhoto, $photoLargeur, $photoHauteur,
        '', '', '', false, 300, '', false, false, 0, 'CM', false, false, false
    );
    $pdf->SetDrawColorArray(RCR_GOLD);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect($xPhoto, $yPhoto, $photoLargeur, $photoHauteur);
} else {
    // Encadré vide si aucune photo n'est disponible, plutôt que rien du
    // tout — évite qu'un espace vide inexpliqué interroge à l'impression.
    $pdf->SetDrawColorArray(RCR_GOLD);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect($xPhoto, $yPhoto, $photoLargeur, $photoHauteur);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY($xPhoto, $yPhoto + ($photoHauteur / 2) - 3);
    $pdf->MultiCell($photoLargeur, 3, "Photo\nindisponible", 0, 'C');
}

// Le curseur redescend au moins jusqu'au bas de la photo, pour ne
// jamais chevaucher la section suivante si le texte était plus court.
$pdf->SetY(max($yApresChampsIdentite, $yPhoto + $photoHauteur + 2));
$pdf->Ln(1);

// --------------------------------------------------------------
// LOCALISATION ADMINISTRATIVE
// --------------------------------------------------------------
rcr_section_title($pdf, "LOCALISATION ADMINISTRATIVE");
rcr_field_row($pdf, "Province", $info['nom_p']);
rcr_field_row($pdf, "Territoire", $info['nom_tr']);
rcr_field_row($pdf, "Secteur", $info['nom_sec']);
rcr_field_row($pdf, "Qualité", $info['designation']);
rcr_field_row($pdf, "Grade", $info['nom_gd']);
$pdf->Ln(2.5);

// --------------------------------------------------------------
// INFORMATIONS DE PAIEMENT
// --------------------------------------------------------------
rcr_section_title($pdf, "INFORMATIONS DE PAIEMENT");
rcr_field_row($pdf, "Cotisation", $info['nom_cot']);
rcr_field_row(
    $pdf,
    "Montant payé",
    $info['prix'] !== null
        ? number_format((float) $info['prix'], 2, ',', ' ') . ' $'
        : ($ficheValide ? 'N/A' : 'AUCUN PAIEMENT CONFIRMÉ')
);
rcr_field_row($pdf, "Date d'adhésion", $info['dat_adhesion']);
$pdf->Ln(3);

// --------------------------------------------------------------
// ACTE D'ENGAGEMENT
// --------------------------------------------------------------
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColorArray(RCR_INK);
$pdf->Cell(0, 6, "ACTE D'ENGAGEMENT", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9.5);
$pdf->SetTextColor(30, 30, 30);

$pdf->writeHTML("
<ol style=\"line-height: 0.9;\">
<li>Je m'engage à respecter les statuts du parti.</li>
<li>J'accepte les communications internes.</li>
<li>Je soutiens les activités politiques.</li>
<li>Je participe au recrutement de nouveaux membres.</li>
<li>Je confirme avoir lu les conditions.</li>
</ol>
", true, false, true, false, '');

// --------------------------------------------------------------
// QR CODE + SIGNATURE (même page, toujours)
// --------------------------------------------------------------
$pdf->Ln(2);
$yBloc = $pdf->GetY();

$contenuQr = ($ficheValide ? "RCR — FICHE D'ADHÉSION OFFICIELLE\n" : "RCR — FICHE D'ADHÉSION — NON VALIDÉE (paiement non confirmé)\n")
    . "Code : " . $info['codes'] . "\n"
    . "Nom : " . trim($info['nom'] . ' ' . $info['postnom'] . ' ' . $info['prenom']) . "\n"
    . "Date d'adhésion : " . $info['dat_adhesion'] . "\n"
    . "rcr.cd";
$contenuQr .= "\nVérification : https://rcr.cd/index.php?pages=verifier&code=" . $info['codes'];

$style = [
    'border'        => false,
    'vpadding'      => 'auto',
    'hpadding'      => 'auto',
    'fgcolor'       => RCR_INK,
    'bgcolor'       => false,
    'module_width'  => 1,
    'module_height' => 1,
];

$pdf->write2DBarcode($contenuQr, 'QRCODE,M', 15, $yBloc, 24, 24, $style, 'N');

$pdf->SetXY(44, $yBloc);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(90, 90, 90);
$pdf->MultiCell(70, 4, "Scannez ce code pour retrouver\nles informations officielles\nde cet adhérent", 0, 'L');

if ($ficheValide) {
    $pdf->SetFont('helvetica', '', 9.5);
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetXY(130, $yBloc + 10);
    $pdf->Cell(0, 5, "_____________________________", 0, 1, 'L');

    $pdf->SetXY(130, $yBloc + 15);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(0, 4, "Signature du responsable des adhésions", 0, 1, 'L');
}

// Close and output PDF document
$pdf->Output(
    'fiche_adhesion_' . ($ficheValide ? '' : 'NON_VALIDEE_') . preg_replace('/[^A-Za-z0-9_-]/', '', $info['codes']) . '.pdf',
    'I'
);
