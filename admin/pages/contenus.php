<?php
/**
 * Administration → Contenus du site : tous les textes publics viennent de la base.
 *   Onglet « Textes »     : blocs de pages (site_blocs), brouillon / publié / archivé
 *   Onglet « Réglages »   : nom du parti, devise, titres de l'accueil, boutons, contacts, SEO (site_reglages)
 *   Onglet « Menu »       : navigation publique (site_menu)
 * Permission : contenu.gerer. Chaque modification est journalisée et vide le cache.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'contenu.gerer');

const CT_LANGUES = ['fr' => 'Français', 'ln' => 'Lingala', 'sw' => 'Swahili', 'kg' => 'Kikongo', 'lu' => 'Tshiluba'];
$onglet = in_array($_GET['onglet'] ?? '', ['textes', 'reglages', 'menu'], true) ? $_GET['onglet'] : 'textes';
$zone   = (($_GET['zone'] ?? $_POST['zone'] ?? '') === 'footer') ? 'footer' : 'header';
$langue = array_key_exists($_GET['langue'] ?? '', CT_LANGUES) ? $_GET['langue'] : 'fr';
$idAdm  = (int) $_SESSION['id_adm'];

function ct_url_valide(string $u): bool
{
    return $u === '#' || $u === '@renouveler'
        || (bool) preg_match('#^\?pages=[a-z0-9_\-]+(&[a-z_]+=[A-Za-z0-9_\-%]+)*(\#[A-Za-z0-9_\-]+)?$#i', $u)
        || (bool) preg_match('#^\./?[A-Za-z0-9_\-/.]+(\?[A-Za-z0-9_=&\-]*)?(\#[A-Za-z0-9_\-]+)?$#', $u)
        || (bool) preg_match('#^https://[^\s"<>]+$#i', $u)
        || (bool) preg_match('#^(mailto:[^\s"<>]+@[^\s"<>]+|tel:\+?[0-9 ]{6,20})$#i', $u);
}

/* --------------------------------------------------------------- POST */
if (admin_post_guard($bdd, 'contenu.gerer')) {
    $act = (string) ($_POST['action'] ?? '');

    if ($act === 'bloc') {
        $page = strtolower(trim((string) ($_POST['page'] ?? '')));
        $cle  = strtolower(trim((string) ($_POST['cle'] ?? '')));
        $lg   = array_key_exists($_POST['langue'] ?? '', CT_LANGUES) ? $_POST['langue'] : 'fr';
        if (!preg_match('/^[a-z0-9_\-]{1,80}$/', $page) || !preg_match('/^[a-z0-9_\-]{1,80}$/', $cle)) {
            admin_flash('Identifiants invalides (lettres minuscules, chiffres, - et _ uniquement).', 'danger');
        } else {
            $titre = mb_substr(trim((string) ($_POST['titre'] ?? '')), 0, 255);
            $corps = (string) ($_POST['contenu'] ?? '');
            if (strlen($corps) > 500000) {
                admin_flash('Contenu trop volumineux.', 'danger');
            } else {
                bloc_enregistrer($bdd, $page, $cle, $lg, $titre, $corps, (string) ($_POST['type'] ?? 'html'), (string) ($_POST['statut'] ?? 'brouillon'), $idAdm);
                admin_flash('Texte enregistré.');
            }
        }
        admin_redirect('pages=contenus&onglet=textes&page_c=' . urlencode($page) . '&langue=' . urlencode($lg));
    }

    if ($act === 'reglages') {
        $n = 0;
        // Clés connues = réglages français (référence) ; seules les valeurs réellement modifiées sont enregistrées.
        $s = $bdd->prepare("SELECT f.cle, COALESCE(l.valeur, '') AS valeur, l.cle IS NOT NULL AS existe
                            FROM site_reglages f LEFT JOIN site_reglages l ON l.cle = f.cle AND l.langue = ?
                            WHERE f.langue = 'fr'");
        $s->execute([$langue]);
        $actuels = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $actuels[$r['cle']] = $r; }
        foreach ((array) ($_POST['r'] ?? []) as $cle => $val) {
            if (!isset($actuels[$cle]) || !is_string($val)) { continue; }
            $val = mb_substr(trim($val), 0, 5000);
            if ($val === $actuels[$cle]['valeur'] && ($actuels[$cle]['existe'] || $val === '')) { continue; }
            reglage_enregistrer($bdd, (string) $cle, $langue, $val, $idAdm);
            $n++;
        }
        admin_flash($n > 0 ? "$n réglage(s) enregistré(s)." : 'Aucune modification.', $n > 0 ? 'success' : 'info');
        admin_redirect('pages=contenus&onglet=reglages&langue=' . urlencode($langue));
    }

    if ($act === 'menu_save' || $act === 'menu_add') {
        $lib = mb_substr(trim((string) ($_POST['libelle'] ?? '')), 0, 120);
        $url = trim((string) ($_POST['url'] ?? ''));
        $parent = (int) ($_POST['parent_id'] ?? 0) ?: null;
        $ordre = (int) ($_POST['ordre'] ?? 0);
        $actif = isset($_POST['actif']) ? 1 : 0;
        if ($lib === '' || !ct_url_valide($url)) {
            admin_flash('Libellé vide ou adresse invalide (ex. ?pages=contact, ./adhere/adhesion.php, https://…).', 'danger');
        } elseif ($act === 'menu_add') {
            $bdd->prepare("INSERT INTO site_menu (zone, parent_id, langue, libelle, url, ordre, actif) VALUES (?, ?, 'fr', ?, ?, ?, ?)")->execute([$zone, $parent, $lib, $url, $ordre, $actif]);
            contenu_cache_purge(); audit_log($bdd, 'menu.ajouter', 'site_menu', '', ['libelle' => $lib, 'url' => $url]);
            admin_flash('Entrée de menu ajoutée.');
        } else {
            $id = (int) ($_POST['id'] ?? 0);
            if ($parent === $id) { $parent = null; }
            $bdd->prepare('UPDATE site_menu SET libelle = ?, url = ?, parent_id = ?, ordre = ?, actif = ? WHERE id = ? AND zone = ?')->execute([$lib, $url, $parent, $ordre, $actif, $id, $zone]);
            contenu_cache_purge(); audit_log($bdd, 'menu.modifier', 'site_menu', (string) $id, ['libelle' => $lib, 'url' => $url]);
            admin_flash('Entrée de menu enregistrée.');
        }
        admin_redirect('pages=contenus&onglet=menu&zone=' . $zone);
    }

    if ($act === 'menu_del') {
        $id = (int) ($_POST['id'] ?? 0);
        $bdd->prepare('UPDATE site_menu SET parent_id = NULL WHERE parent_id = ?')->execute([$id]);
        $bdd->prepare('DELETE FROM site_menu WHERE id = ?')->execute([$id]);
        contenu_cache_purge(); audit_log($bdd, 'menu.supprimer', 'site_menu', (string) $id);
        admin_flash('Entrée supprimée.');
        admin_redirect('pages=contenus&onglet=menu&zone=' . $zone);
    }
}

/* --------------------------------------------------------------- données */
$tabs = ['textes' => 'Textes des pages', 'reglages' => 'Réglages & coordonnées', 'menu' => 'Menus (en-tête / pied de page)'];
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <ul class="nav nav-tabs mb-3">
        <?php foreach ($tabs as $k => $l): ?><li class="nav-item"><a class="nav-link <?= $onglet === $k ? 'active' : '' ?>" href="?pages=contenus&amp;onglet=<?= $k ?>&amp;langue=<?= e($langue) ?>"><?= e($l) ?></a></li><?php endforeach; ?>
        <li class="nav-item ms-auto d-flex align-items-center gap-2">
            <?php foreach (CT_LANGUES as $k => $l): if ($onglet === 'menu') { break; } ?><a class="badge text-bg-<?= $langue === $k ? 'primary' : 'light border text-dark' ?> text-decoration-none" href="?pages=contenus&amp;onglet=<?= e($onglet) ?>&amp;langue=<?= $k ?>"><?= e($l) ?></a><?php endforeach; ?>
        </li>
    </ul>

<?php if ($onglet === 'textes'):
    $pages = $bdd->query('SELECT page, COUNT(*) n FROM site_blocs GROUP BY page ORDER BY page')->fetchAll(PDO::FETCH_KEY_PAIR);
    $pageC = strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string) ($_GET['page_c'] ?? ($pages ? array_key_first($pages) : 'home'))));
    $s = $bdd->prepare('SELECT * FROM site_blocs WHERE page = ? AND langue = ? ORDER BY ordre, id'); $s->execute([$pageC, $langue]);
    $blocs = $s->fetchAll(PDO::FETCH_ASSOC);
    $cleC = strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string) ($_GET['cle'] ?? '')));
    $edit = null;
    foreach ($blocs as $b) { if ($b['cle'] === $cleC) { $edit = $b; } }
    $nouveau = isset($_GET['nouveau']);
    $badge = ['publie' => 'success', 'brouillon' => 'warning', 'archive' => 'secondary'];
?>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><form method="get" class="d-flex gap-2">
                    <input type="hidden" name="pages" value="contenus"><input type="hidden" name="langue" value="<?= e($langue) ?>">
                    <select name="page_c" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($pages as $pg => $n): ?><option value="<?= e($pg) ?>" <?= $pg === $pageC ? 'selected' : '' ?>><?= e($pg) ?> (<?= (int) $n ?>)</option><?php endforeach; ?>
                        <?php if (!isset($pages[$pageC])): ?><option selected><?= e($pageC) ?></option><?php endif; ?>
                    </select></form></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($blocs as $b): ?>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $edit && $edit['id'] === $b['id'] ? 'active' : '' ?>"
                           href="?pages=contenus&amp;onglet=textes&amp;langue=<?= e($langue) ?>&amp;page_c=<?= e($pageC) ?>&amp;cle=<?= e($b['cle']) ?>">
                            <span class="text-truncate"><?= e($b['titre'] !== '' ? $b['titre'] : $b['cle']) ?></span>
                            <span class="badge text-bg-<?= $badge[$b['statut']] ?? 'light' ?>"><?= e($b['statut']) ?></span></a>
                    <?php endforeach; if (!$blocs): ?><div class="list-group-item text-muted small">Aucun texte dans cette langue. Pour traduire, créez un texte avec la même clé que la version française.</div><?php endif; ?>
                </div>
                <div class="card-footer bg-white"><a class="btn btn-sm btn-success" href="?pages=contenus&amp;onglet=textes&amp;langue=<?= e($langue) ?>&amp;page_c=<?= e($pageC) ?>&amp;nouveau=1"><i class="bi bi-plus-lg"></i> Nouveau texte</a></div>
            </div>
        </div>
        <div class="col-lg-8">
        <?php if ($edit || $nouveau): $b = $edit ?: ['cle' => '', 'titre' => '', 'contenu' => '', 'type' => 'html', 'statut' => 'brouillon']; ?>
            <form method="post" class="card border-0 shadow-sm"><?= csrf_field() ?>
                <input type="hidden" name="action" value="bloc"><input type="hidden" name="langue" value="<?= e($langue) ?>">
                <div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label small">Page</label><input name="page" class="form-control" value="<?= e($pageC) ?>" <?= $edit ? 'readonly' : '' ?> required></div>
                    <div class="col-md-6"><label class="form-label small">Clé (identifiant unique)</label><input name="cle" class="form-control" value="<?= e($b['cle']) ?>" <?= $edit ? 'readonly' : '' ?> required pattern="[a-z0-9_\-]+"></div>
                    <div class="col-12"><label class="form-label small">Titre</label><input name="titre" class="form-control" maxlength="255" value="<?= e($b['titre']) ?>"></div>
                    <div class="col-md-6"><label class="form-label small">Format</label><select name="type" class="form-select">
                        <option value="html" <?= $b['type'] === 'html' ? 'selected' : '' ?>>HTML (mise en forme)</option><option value="texte" <?= $b['type'] === 'texte' ? 'selected' : '' ?>>Texte simple</option></select></div>
                    <div class="col-md-6"><label class="form-label small">Statut</label><select name="statut" class="form-select">
                        <?php foreach (['publie' => 'Publié', 'brouillon' => 'Brouillon', 'archive' => 'Archivé'] as $k => $l): ?><option value="<?= $k ?>" <?= $b['statut'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label small">Contenu</label><textarea name="contenu" rows="18" class="form-control font-monospace" style="font-size:.85rem"><?= e($b['contenu']) ?></textarea>
                        <div class="form-text">Seuls les contenus « Publié » apparaissent sur le site. Les scripts et attributs dangereux sont retirés automatiquement.</div></div>
                </div>
                <div class="card-footer bg-white"><button class="btn btn-primary">Enregistrer</button></div>
            </form>
        <?php else: ?><div class="card border-0 shadow-sm"><div class="card-body text-muted">Choisissez un texte à gauche, ou créez-en un nouveau.</div></div><?php endif; ?>
        </div>
    </div>

<?php elseif ($onglet === 'reglages'):
    // Liste de référence = réglages français ; la valeur affichée est celle de la langue choisie (vide = traduction à saisir).
    $s = $bdd->prepare("SELECT f.cle, COALESCE(l.valeur, IF(? = 'fr', f.valeur, '')) AS valeur, f.groupe, f.libelle, f.type, f.valeur AS valeur_fr
                        FROM site_reglages f LEFT JOIN site_reglages l ON l.cle = f.cle AND l.langue = ?
                        WHERE f.langue = 'fr' ORDER BY f.groupe, f.cle");
    $s->execute([$langue, $langue]);
    $groupes = [];
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $groupes[$r['groupe']][] = $r; }
    $titresG = ['identite' => 'Identité du parti', 'accueil' => 'Page d\'accueil', 'valeurs' => 'Valeurs', 'boutons' => 'Boutons', 'contact' => 'Contacts, adresse et réseaux sociaux', 'pages' => 'Titres et introductions des pages', 'seo' => 'Référencement (SEO)', 'general' => 'Général'];
?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="reglages">
    <?php foreach ($groupes as $g => $liste): ?>
        <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold"><?= e($titresG[$g] ?? $g) ?></div>
            <div class="card-body row g-3">
            <?php foreach ($liste as $r): ?>
                <div class="col-md-6"><label class="form-label small"><?= e($r['libelle'] !== '' ? $r['libelle'] : $r['cle']) ?> <span class="text-muted">(<?= e($r['cle']) ?>)</span></label>
                    <?php if ($r['type'] === 'html' || mb_strlen($r['valeur']) > 90): ?><textarea name="r[<?= e($r['cle']) ?>]" rows="3" class="form-control"><?= e($r['valeur']) ?></textarea>
                    <?php else: ?><input name="r[<?= e($r['cle']) ?>]" class="form-control" value="<?= e($r['valeur']) ?>"<?= $langue !== 'fr' ? ' placeholder="' . e($r['valeur_fr']) . '"' : '' ?>><?php endif; ?></div>
            <?php endforeach; ?></div></div>
    <?php endforeach; if (!$groupes): ?><div class="alert alert-info">Aucun réglage dans cette langue. Les réglages sont créés en français lors de la migration ; les traductions s'ajoutent ensuite.</div><?php endif; ?>
        <div class="position-sticky bottom-0 bg-white border-top p-3"><button class="btn btn-primary">Enregistrer les réglages</button></div>
    </form>

<?php else:
    $s = $bdd->prepare("SELECT * FROM site_menu WHERE zone = ? AND langue = 'fr' ORDER BY COALESCE(parent_id, id), parent_id IS NOT NULL, ordre, id");
    $s->execute([$zone]);
    $items = $s->fetchAll(PDO::FETCH_ASSOC);
    $racines = array_filter($items, fn($i) => $i['parent_id'] === null);
?>
    <div class="btn-group mb-3" role="group" aria-label="Zone du menu">
        <a class="btn btn-sm <?= $zone === 'header' ? 'btn-primary' : 'btn-outline-primary' ?>" href="?pages=contenus&amp;onglet=menu&amp;zone=header">Menu de l'en-tête</a>
        <a class="btn btn-sm <?= $zone === 'footer' ? 'btn-primary' : 'btn-outline-primary' ?>" href="?pages=contenus&amp;onglet=menu&amp;zone=footer">Liens du pied de page</a>
    </div>
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">
        <?= $zone === 'header' ? 'Entrées du menu (une entrée « # » avec des sous-entrées devient un menu déroulant)' : 'Colonnes du pied de page : une entrée principale = titre de colonne, ses sous-entrées = liens (2 colonnes affichées)' ?>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>Libellé</th><th>Adresse</th><th><?= $zone === 'header' ? 'Sous-menu de' : 'Colonne' ?></th><th style="width:90px">Ordre</th><th>Actif</th><th></th></tr></thead>
        <tbody>
        <?php if (!$items): ?><tr><td colspan="6" class="text-muted p-3">Aucune entrée : <?= $zone === 'footer' ? 'le pied de page affiche ses liens par défaut.' : 'le site affiche son menu par défaut.' ?></td></tr><?php endif; ?>
        <?php foreach ($items as $i): $fid = 'm' . (int) $i['id']; ?>
            <tr>
                <td><input form="<?= $fid ?>" name="libelle" class="form-control form-control-sm" value="<?= e($i['libelle']) ?>" aria-label="Libellé" <?= $i['parent_id'] ? 'style="margin-left:1rem;width:calc(100% - 1rem)"' : '' ?>></td>
                <td><input form="<?= $fid ?>" name="url" class="form-control form-control-sm" value="<?= e($i['url']) ?>" aria-label="Adresse"></td>
                <td><select form="<?= $fid ?>" name="parent_id" class="form-select form-select-sm" aria-label="Parent"><option value="">— (niveau principal)</option>
                    <?php foreach ($racines as $r): if ((int) $r['id'] === (int) $i['id']) { continue; } ?><option value="<?= (int) $r['id'] ?>" <?= (int) $i['parent_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option><?php endforeach; ?></select></td>
                <td><input form="<?= $fid ?>" type="number" name="ordre" class="form-control form-control-sm" value="<?= (int) $i['ordre'] ?>" aria-label="Ordre"></td>
                <td><input form="<?= $fid ?>" type="checkbox" class="form-check-input" name="actif" value="1" <?= $i['actif'] ? 'checked' : '' ?> aria-label="Actif"></td>
                <td class="text-nowrap">
                    <form id="<?= $fid ?>" method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="menu_save"><input type="hidden" name="zone" value="<?= e($zone) ?>"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button></form>
                    <form method="post" class="d-inline js-confirm" data-confirm="Supprimer cette entrée de menu ?"><?= csrf_field() ?><input type="hidden" name="action" value="menu_del"><input type="hidden" name="zone" value="<?= e($zone) ?>"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn btn-sm btn-outline-danger" aria-label="Supprimer"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
    <form method="post" class="card border-0 shadow-sm"><?= csrf_field() ?><input type="hidden" name="action" value="menu_add"><input type="hidden" name="zone" value="<?= e($zone) ?>">
        <div class="card-header bg-white fw-semibold">Ajouter une entrée</div>
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Libellé</label><input name="libelle" class="form-control" required maxlength="120"></div>
            <div class="col-md-3"><label class="form-label small">Adresse</label><input name="url" class="form-control" required placeholder="?pages=contact"></div>
            <div class="col-md-3"><label class="form-label small"><?= $zone === 'header' ? 'Sous-menu de' : 'Colonne' ?></label><select name="parent_id" class="form-select"><option value="">— (niveau principal)</option>
                <?php foreach ($racines as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e($r['libelle']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-1"><label class="form-label small">Ordre</label><input type="number" name="ordre" class="form-control" value="10"></div>
            <div class="col-md-1"><div class="form-check"><input type="checkbox" class="form-check-input" name="actif" value="1" checked id="menu_actif"><label class="form-check-label small" for="menu_actif">Actif</label></div></div>
            <div class="col-md-1"><button class="btn btn-success w-100">Ajouter</button></div>
        </div></form>
<?php endif; ?>
</div>
