<?php
/**
 * Traitement du formulaire d'adhésion (adhere/adhesion.php).
 *
 * Reprend la logique de l'ancienne version (codes membre, uploads, parrain,
 * encadreur) avec ces changements :
 *  - catégorie / grade / période validés et le MONTANT recalculé côté serveur (tarifs.php) ;
 *  - création du compte membre (mot de passe : password_hash) ;
 *  - uploads vérifiés par type MIME réel, noms générés ;
 *  - parrain (?idmbre ou id_dest) ET encadreur (id_dest2) réellement enregistrés ;
 *  - données stockées brutes, échappées à l'affichage (plus de double échappement).
 */
require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/tarifs.php';

/* ---- Données du sélecteur (catégories > grades actifs ; périodes actives) ---- */
$TARIFS = ['categories' => [], 'periodes' => []];
foreach (tarifs_categories($bdd) as $c) {
    $grades = tarifs_grades($bdd, (int) $c['id_qt']);
    if (!$grades) {
        continue;
    }
    $TARIFS['categories'][] = [
        'id'     => (int) $c['id_qt'],
        'nom'    => $c['designation'],
        'grades' => array_map(fn($g) => ['id' => (int) $g['id_gd'], 'nom' => $g['nom_gd'], 'prix' => (float) $g['prix']], $grades),
    ];
}
foreach (tarifs_periodes($bdd) as $p) {
    $TARIFS['periodes'][] = ['id' => (int) $p['id_cot'], 'nom' => $p['nom_cot'], 'mois' => (int) $p['mois']];
}

if (!function_exists('adhesion_fail')) {
    function adhesion_fail(string $message): void
    {
        $_SESSION['adhesion_erreur'] = $message;
        header('Location: adhesion.php');
        exit;
    }
}

if (!function_exists('adhesion_save_upload')) {
    /** Vérifie (taille, MIME réel, extension) puis enregistre sous un nom généré. Retourne le nom de fichier. */
    function adhesion_save_upload(array $file, array $mimeToExt, string $dir, string $prefix, int $maxBytes): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Le téléversement a échoué.');
        }
        if ($file['size'] > $maxBytes) {
            throw new RuntimeException('Fichier trop volumineux (' . (int) ($maxBytes / 1048576) . ' Mo maximum).');
        }
        $mime = mime_reel($file['tmp_name']);
        if (!isset($mimeToExt[$mime])) {
            throw new RuntimeException('Type de fichier non autorisé.');
        }
        if (strpos($mime, 'image/') === 0 && @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Image invalide.');
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = $prefix . '_' . bin2hex(random_bytes(10)) . '.' . $mimeToExt[$mime];
        if (!move_uploaded_file($file['tmp_name'], rtrim($dir, '/') . '/' . $name)) {
            throw new RuntimeException('Enregistrement du fichier impossible.');
        }
        return $name;
    }
}

if (isset($_POST['btnenvoyer'])) {
    if (!csrf_verify()) {
        adhesion_fail('Session expirée : merci de recommencer.');
    }

    $v = static fn(string $k): string => trim((string) ($_POST[$k] ?? ''));

    $nom = $v('nom'); $postnom = $v('postnom'); $prenom = $v('prenom');
    $mail = mb_strtolower($v('mail')); $nationalite = $v('nationalite'); $civilite = $v('civilite');
    $telephone = $v('telephone'); $datenaiss = $v('datenaiss'); $adresse = $v('adresse'); $ville = $v('ville');
    $codepostal = $v('codepostal'); $categorie = $v('categorie'); $diplome = $v('diplome');
    $idQt = (int) $v('id_qt'); $idGd = (int) $v('grade'); $idCot = (int) $v('reglement');
    $province = (int) $v('province'); $territoire = (int) $v('territoire'); $secteur = (int) $v('secteur');
    $mdp = (string) ($_POST['mot_de_passe'] ?? ''); $mdp2 = (string) ($_POST['mot_de_passe2'] ?? '');

    foreach ([$nom, $postnom, $prenom, $mail, $nationalite, $civilite, $telephone, $datenaiss, $adresse] as $champ) {
        if ($champ === '') {
            adhesion_fail('Veuillez compléter tous les champs obligatoires.');
        }
    }
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) { adhesion_fail('Adresse e-mail invalide.'); }
    if (!preg_match('/^\+?[0-9 ]{9,16}$/', $telephone)) { adhesion_fail('Numéro de téléphone invalide.'); }
    $d = DateTime::createFromFormat('Y-m-d', $datenaiss);
    if (!$d || $d->format('Y-m-d') !== $datenaiss || $d > new DateTime('today')) { adhesion_fail('Date de naissance invalide.'); }
    if (strlen($mdp) < 8 || $mdp !== $mdp2) { adhesion_fail('Mot de passe : 8 caractères minimum, les deux saisies doivent être identiques.'); }
    if (empty($_POST['acceptation_legale'])) { adhesion_fail('Vous devez accepter les mentions légales et la politique de confidentialité.'); }

    // Tarif : TOUJOURS recalculé ici, jamais lu depuis le navigateur
    $tarif = tarifs_calculer($bdd, $idQt, $idGd, $idCot);
    if (!$tarif) { adhesion_fail('Catégorie, grade ou mode de cotisation indisponible. Veuillez refaire votre choix.'); }

    $q = $bdd->prepare('SELECT designation FROM qualites WHERE id_qt = ?'); $q->execute([$idQt]); $qlt = $q->fetch(PDO::FETCH_ASSOC);
    $p = $bdd->prepare('SELECT lettre FROM provinces WHERE id_p = ?'); $p->execute([$province]); $pr = $p->fetch(PDO::FETCH_ASSOC);
    $t = $bdd->prepare('SELECT id_tr FROM territoires WHERE id_tr = ? AND id_p = ?'); $t->execute([$territoire, $province]);
    $s = $bdd->prepare('SELECT id_sec FROM secteurs WHERE id_sec = ? AND id_tr = ?'); $s->execute([$secteur, $territoire]);
    if (!$qlt || !$pr || !$t->fetchColumn() || !$s->fetchColumn()) {
        adhesion_fail('Localisation invalide (province, territoire ou secteur).');
    }

    $m = $bdd->prepare('SELECT id_ad FROM adhesion WHERE mail = ?'); $m->execute([$mail]);
    if ($m->fetchColumn()) { adhesion_fail('Cette adresse e-mail est déjà utilisée. Connectez-vous à votre espace membre pour renouveler.'); }

    $photo = $cv = '';
    try {
        $photo = adhesion_save_upload($_FILES['passeport'] ?? [], ['image/jpeg' => 'jpg', 'image/png' => 'png'], __DIR__ . '/../media/passeport', 'Photo', 10 * 1048576);
        if (!empty($_FILES['cv']['name'])) {
            $cv = adhesion_save_upload($_FILES['cv'], ['application/pdf' => 'pdf'], __DIR__ . '/../media/cv', 'CV', 10 * 1048576);
        }
    } catch (RuntimeException $e) {
        adhesion_fail('Pièces jointes : ' . $e->getMessage());
    }

    $token = bin2hex(random_bytes(32));
    $codeProvisoire = 'TMP' . strtoupper(bin2hex(random_bytes(8))); // remplacé juste après par le code définitif

    $bdd->beginTransaction();
    try {
        $ins = $bdd->prepare('INSERT INTO adhesion
            (codes, nom, postnom, prenom, mail, nationalite, civilite, id_qt, grade, codepostal, secteur, territoire, province,
             reglement, telephone, datenaiss, categorie, diplome, passeport, cv, adresse, ville, payment_token, password_hash, statut, id_cot, dat_adhesion)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'en_attente\',?,NOW())');
        $ins->execute([$codeProvisoire, $nom, $postnom, $prenom, $mail, $nationalite, $civilite, $idQt, $idGd, $codepostal, $secteur, $territoire,
            $province, $idCot, $telephone, $datenaiss, $categorie, $diplome, $photo, $cv, $adresse, $ville, $token,
            password_hash($mdp, PASSWORD_DEFAULT), $idCot]);
        $idAd = (int) $bdd->lastInsertId();

        // Code membre : 1re lettre de la catégorie + n° (6 chiffres) + secteur (4) + lettre province
        $code = strtoupper(substr($qlt['designation'], 0, 1) . str_pad((string) $idAd, 6, '0', STR_PAD_LEFT)
                . str_pad((string) $secteur, 4, '0', STR_PAD_LEFT) . substr($pr['lettre'], 0, 1));
        $bdd->prepare('UPDATE adhesion SET codes = ? WHERE id_ad = ?')->execute([$code, $idAd]);

        // Parrain (lien de parrainage ?idmbre=… ou recherche) et encadreur
        $parrain = filter_var($_GET['idmbre'] ?? ($_POST['id_dest'] ?? null), FILTER_VALIDATE_INT);
        if ($parrain && $parrain !== $idAd) {
            $bdd->prepare('INSERT INTO parner (id_exp, id_dest) VALUES (?, ?)')->execute([$idAd, $parrain]);
        }
        $encadreur = filter_var($_POST['id_dest2'] ?? null, FILTER_VALIDATE_INT);
        $placement = in_array($_POST['placement'] ?? '', ['A', 'B'], true) ? $_POST['placement'] : '';
        if ($encadreur && $encadreur !== $idAd) {
            $bdd->prepare('INSERT INTO encadreur (id_exp, id_dest, placement) VALUES (?, ?, ?)')->execute([$idAd, $encadreur, $placement]);
        }
        $bdd->commit();
    } catch (Throwable $e) {
        $bdd->rollBack();
        error_log('[adhesion] ' . $e->getMessage());
        adhesion_fail('Une erreur est survenue. Veuillez réessayer.');
    }

    session_regenerate_id(true);
    $_SESSION['payment_token'] = $token;
    // Le nouvel adhérent peut télécharger SA fiche (non validée tant que le paiement n'est pas confirmé)
    // depuis les pages de paiement, sans pouvoir accéder à celle d'un autre (contrôle dans print_adherer.php).
    $_SESSION['adhesion_recente'] = $idAd;
    header('Location: paiement.php?token=' . $token);
    exit;
}
