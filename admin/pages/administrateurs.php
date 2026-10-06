<?php
/**
 * Administration → Administrateurs et rôles. Permission : admins.gerer.
 *
 * Garde-fous :
 *  - seul un « administrateur principal » peut créer, promouvoir, modifier ou désactiver un compte administrateur principal
 *    (sinon un responsable numérique pourrait s'octroyer tous les droits) ;
 *  - on ne peut ni se désactiver ni changer son propre rôle ;
 *  - il reste toujours au moins un administrateur principal actif ;
 *  - aucun compte n'est supprimé (désactivation seulement), tout est journalisé.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'admins.gerer');

$moi       = (int) $_SESSION['id_adm'];
$estPrinc  = rbac_role_code($bdd) === 'admin_principal';
$roles     = $bdd->query('SELECT id_role, code, libelle, fonction FROM roles ORDER BY id_role')->fetchAll(PDO::FETCH_ASSOC);
$rolesParId = array_column($roles, null, 'id_role');

$roleDe = function (int $idAdm) use ($bdd): ?string {
    $s = $bdd->prepare('SELECT r.code, a.niveau FROM admin a LEFT JOIN roles r ON r.id_role = a.id_role WHERE a.id_adm = ?');
    $s->execute([$idAdm]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    return $r ? ($r['code'] ?: (NIVEAU_VERS_ROLE[(int) $r['niveau']] ?? null)) : null;
};
$nbPrincipauxActifs = function () use ($bdd): int {
    $n = 0;
    foreach ($bdd->query('SELECT a.niveau, a.confirmer, r.code FROM admin a LEFT JOIN roles r ON r.id_role = a.id_role')->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if ((int) $r['confirmer'] === 1 && ($r['code'] ?: (NIVEAU_VERS_ROLE[(int) $r['niveau']] ?? null)) === 'admin_principal') { $n++; }
    }
    return $n;
};

if (admin_post_guard($bdd, 'admins.gerer')) {
    $act = (string) ($_POST['action'] ?? '');
    $id  = (int) ($_POST['id_adm'] ?? 0);
    $idRole = (int) ($_POST['id_role'] ?? 0);
    $role = $rolesParId[$idRole] ?? null;
    $erreur = null;

    if ($act === 'creer') {
        $pseudo = trim((string) ($_POST['pseudo'] ?? '')); $mail = trim((string) ($_POST['mail'] ?? '')); $mdp = (string) ($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,30}$/', $pseudo)) { $erreur = 'Pseudo invalide (3 à 30 caractères : lettres, chiffres, . _ -).'; }
        elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL) || strlen($mail) > 120) { $erreur = 'Adresse e-mail invalide.'; }
        elseif (strlen($mdp) < 12) { $erreur = 'Mot de passe : 12 caractères minimum.'; }
        elseif (!$role) { $erreur = 'Rôle invalide.'; }
        elseif ($role['code'] === 'admin_principal' && !$estPrinc) { $erreur = 'Seul un administrateur principal peut créer un administrateur principal.'; }
        else {
            $d = $bdd->prepare('SELECT COUNT(*) FROM admin WHERE pseudo = ? OR mail = ?'); $d->execute([$pseudo, $mail]);
            if ((int) $d->fetchColumn() > 0) { $erreur = 'Ce pseudo ou cet e-mail existe déjà.'; }
            else {
                $bdd->prepare("INSERT INTO admin (pseudo, mail, password, fonction, niveau, role, confirmer, id_role) VALUES (?,?,?,?,?,'user',1,?)")
                    ->execute([$pseudo, $mail, password_hash($mdp, PASSWORD_DEFAULT), $role['code'], ROLE_VERS_NIVEAU[$role['code']] ?? 3, $idRole]);
                audit_log($bdd, 'admin.creer', 'admin', (string) $bdd->lastInsertId(), ['pseudo' => $pseudo, 'role' => $role['code']]);
                admin_flash('Administrateur créé.');
            }
        }
    } elseif ($id > 0) {
        $cible = $roleDe($id);
        if ($cible === null) { $erreur = 'Compte introuvable.'; }
        elseif ($cible === 'admin_principal' && !$estPrinc) { $erreur = 'Seul un administrateur principal peut modifier un administrateur principal.'; }
        elseif ($act === 'role') {
            if ($id === $moi) { $erreur = 'Vous ne pouvez pas modifier votre propre rôle.'; }
            elseif (!$role) { $erreur = 'Rôle invalide.'; }
            elseif ($role['code'] === 'admin_principal' && !$estPrinc) { $erreur = 'Seul un administrateur principal peut attribuer ce rôle.'; }
            elseif ($cible === 'admin_principal' && $role['code'] !== 'admin_principal' && $nbPrincipauxActifs() <= 1) { $erreur = 'Il doit rester au moins un administrateur principal actif.'; }
            else {
                $bdd->prepare('UPDATE admin SET id_role = ?, niveau = ?, fonction = ? WHERE id_adm = ?')->execute([$idRole, ROLE_VERS_NIVEAU[$role['code']] ?? 3, $role['code'], $id]);
                audit_log($bdd, 'admin.role', 'admin', (string) $id, ['de' => $cible, 'vers' => $role['code']]);
                admin_flash('Rôle modifié (effectif à sa prochaine connexion ou sous 10 minutes).');
            }
        } elseif ($act === 'activer' || $act === 'desactiver') {
            if ($act === 'desactiver' && $id === $moi) { $erreur = 'Vous ne pouvez pas désactiver votre propre compte.'; }
            elseif ($act === 'desactiver' && $cible === 'admin_principal' && $nbPrincipauxActifs() <= 1) { $erreur = 'Il doit rester au moins un administrateur principal actif.'; }
            else {
                $bdd->prepare('UPDATE admin SET confirmer = ? WHERE id_adm = ?')->execute([$act === 'activer' ? 1 : 0, $id]);
                audit_log($bdd, 'admin.' . $act, 'admin', (string) $id);
                admin_flash($act === 'activer' ? 'Compte activé.' : 'Compte désactivé.');
            }
        } elseif ($act === 'mdp') {
            $mdp = (string) ($_POST['password'] ?? '');
            if (strlen($mdp) < 12) { $erreur = 'Mot de passe : 12 caractères minimum.'; }
            else {
                $bdd->prepare('UPDATE admin SET password = ? WHERE id_adm = ?')->execute([password_hash($mdp, PASSWORD_DEFAULT), $id]);
                audit_log($bdd, 'admin.mot_de_passe', 'admin', (string) $id);
                admin_flash('Mot de passe réinitialisé. Communiquez-le par un canal sûr.');
            }
        }
    }
    if ($erreur) { admin_flash($erreur, 'danger'); }
    admin_redirect('pages=administrateurs');
}

$admins = $bdd->query('SELECT a.id_adm, a.pseudo, a.mail, a.niveau, a.confirmer, a.id_role, r.code, r.libelle FROM admin a LEFT JOIN roles r ON r.id_role = a.id_role ORDER BY a.id_adm')->fetchAll(PDO::FETCH_ASSOC);
$matrice = $bdd->query('SELECT r.id_role, p.code, p.libelle FROM role_permissions rp JOIN roles r ON r.id_role = rp.id_role JOIN permissions p ON p.id_perm = rp.id_perm ORDER BY p.code')->fetchAll(PDO::FETCH_ASSOC);
$perms = []; $matriceR = [];
foreach ($matrice as $m) { $perms[$m['code']] = $m['libelle']; $matriceR[$m['id_role']][$m['code']] = true; }
ksort($perms);
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white fw-semibold">Comptes administrateurs</div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>Pseudo</th><th>E-mail</th><th>Rôle</th><th>État</th><th></th></tr></thead><tbody>
        <?php foreach ($admins as $a):
            $code = $a['code'] ?: (NIVEAU_VERS_ROLE[(int) $a['niveau']] ?? null);
            $verrou = $code === 'admin_principal' && !$estPrinc; ?>
            <tr class="<?= $a['confirmer'] ? '' : 'table-secondary' ?>">
                <td class="fw-semibold"><?= e($a['pseudo']) ?><?= (int) $a['id_adm'] === $moi ? ' <span class="badge text-bg-info">vous</span>' : '' ?></td>
                <td><?= e($a['mail']) ?></td>
                <td>
                    <?php if ($verrou || (int) $a['id_adm'] === $moi): ?><?= e($a['libelle'] ?: ($code ?? 'Non défini')) ?>
                    <?php else: ?><form method="post" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="action" value="role"><input type="hidden" name="id_adm" value="<?= (int) $a['id_adm'] ?>">
                        <select name="id_role" class="form-select form-select-sm"><?php foreach ($roles as $r): if ($r['code'] === 'admin_principal' && !$estPrinc) { continue; } ?>
                            <option value="<?= (int) $r['id_role'] ?>" <?= (int) $a['id_role'] === (int) $r['id_role'] || (!$a['id_role'] && $code === $r['code']) ? 'selected' : '' ?>><?= e($r['libelle']) ?></option><?php endforeach; ?></select>
                        <button class="btn btn-sm btn-outline-primary">OK</button></form><?php endif; ?></td>
                <td><span class="badge text-bg-<?= $a['confirmer'] ? 'success' : 'secondary' ?>"><?= $a['confirmer'] ? 'Actif' : 'Désactivé' ?></span></td>
                <td class="text-nowrap">
                    <?php if (!$verrou): ?>
                        <?php if ((int) $a['id_adm'] !== $moi): ?>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id_adm" value="<?= (int) $a['id_adm'] ?>">
                            <button name="action" value="<?= $a['confirmer'] ? 'desactiver' : 'activer' ?>" class="btn btn-sm btn-outline-<?= $a['confirmer'] ? 'danger' : 'success' ?>" <?= $a['confirmer'] ? 'onclick="return confirm(\'Désactiver ce compte ?\')"' : '' ?>><?= $a['confirmer'] ? 'Désactiver' : 'Activer' ?></button></form>
                        <?php endif; ?>
                        <form method="post" class="d-inline-flex gap-1"><?= csrf_field() ?><input type="hidden" name="action" value="mdp"><input type="hidden" name="id_adm" value="<?= (int) $a['id_adm'] ?>">
                            <input type="password" name="password" minlength="12" required autocomplete="new-password" class="form-control form-control-sm" style="width:150px" placeholder="Nouveau mot de passe">
                            <button class="btn btn-sm btn-outline-secondary">Réinitialiser</button></form>
                    <?php endif; ?></td>
            </tr>
        <?php endforeach; ?></tbody></table></div></div>

    <form method="post" class="card border-0 shadow-sm mb-4"><?= csrf_field() ?><input type="hidden" name="action" value="creer">
        <div class="card-header bg-white fw-semibold">Créer un administrateur</div>
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Pseudo</label><input name="pseudo" class="form-control" required maxlength="30"></div>
            <div class="col-md-3"><label class="form-label small">E-mail</label><input type="email" name="mail" class="form-control" required maxlength="120"></div>
            <div class="col-md-3"><label class="form-label small">Rôle</label><select name="id_role" class="form-select"><?php foreach ($roles as $r): if ($r['code'] === 'admin_principal' && !$estPrinc) { continue; } ?><option value="<?= (int) $r['id_role'] ?>"><?= e($r['libelle']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label small">Mot de passe (12 car. min.)</label><input type="password" name="password" class="form-control" required minlength="12" autocomplete="new-password"></div>
            <div class="col-md-1"><button class="btn btn-success w-100">Créer</button></div>
        </div></form>

    <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Droits par rôle</div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0 text-center">
        <thead class="table-light"><tr><th class="text-start">Permission</th><?php foreach ($roles as $r): ?><th><?= e($r['libelle']) ?><div class="small text-muted fw-normal"><?= e($r['fonction']) ?></div></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach ($perms as $code => $lib): ?><tr><td class="text-start"><?= e($lib) ?> <code class="small"><?= e($code) ?></code></td>
            <?php foreach ($roles as $r): ?><td><?= !empty($matriceR[$r['id_role']][$code]) ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<span class="text-muted">—</span>' ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody></table></div></div>
</div>
