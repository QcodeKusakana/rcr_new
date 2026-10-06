<?php
/**
 * Crée (ou réinitialise) un compte administrateur depuis la ligne de commande.
 *   php migrations/create_admin.php
 * Le mot de passe est saisi au clavier (non affiché) et stocké avec password_hash().
 * Rôles disponibles : admin_principal, resp_publications, gest_effectifs, resp_numerique.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once dirname(__DIR__) . '/config/database.php';

$bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

function ask(string $q, bool $hidden = false): string {
    echo $q;
    if ($hidden && stripos(PHP_OS, 'WIN') !== 0) { system('stty -echo'); }
    $v = trim((string) fgets(STDIN));
    if ($hidden && stripos(PHP_OS, 'WIN') !== 0) { system('stty echo'); echo "\n"; }
    return $v;
}

$pseudo = ask('Pseudo : ');
$mail   = ask('E-mail : ');
$role   = ask('Rôle [admin_principal] : ') ?: 'admin_principal';
$pass   = ask('Mot de passe (12 caractères minimum) : ', true);
$pass2  = ask('Confirmez : ', true);

if ($pseudo === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) { exit("Pseudo ou e-mail invalide.\n"); }
if ($pass !== $pass2 || strlen($pass) < 12) { exit("Mot de passe différent ou trop court.\n"); }

$r = $bdd->prepare('SELECT id_role FROM roles WHERE code = ?');
$r->execute([$role]);
$idRole = (int) $r->fetchColumn();
if ($idRole === 0) { exit("Rôle inconnu (lancez d'abord phase1_migrate.php --apply).\n"); }

$niveau = ['admin_principal' => 7, 'resp_numerique' => 6, 'gest_effectifs' => 5, 'resp_publications' => 4][$role] ?? 3;
$hash = password_hash($pass, PASSWORD_DEFAULT);

$e = $bdd->prepare('SELECT id_adm FROM admin WHERE pseudo = ?');
$e->execute([$pseudo]);
if ($id = $e->fetchColumn()) {
    $bdd->prepare('UPDATE admin SET mail=?, password=?, niveau=?, id_role=?, confirmer=1 WHERE id_adm=?')
        ->execute([$mail, $hash, $niveau, $idRole, $id]);
    echo "Compte « $pseudo » mis à jour.\n";
} else {
    $bdd->prepare('INSERT INTO admin (pseudo, mail, password, fonction, niveau, role, confirmer, id_role) VALUES (?,?,?,?,?,?,1,?)')
        ->execute([$pseudo, $mail, $hash, $role, $niveau, 'user', $idRole]);
    echo "Compte « $pseudo » créé (rôle $role).\n";
}
