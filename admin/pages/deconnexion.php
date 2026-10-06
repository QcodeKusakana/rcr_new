<?php
require_once __DIR__ . '/../../includes/audit.php';
if (!empty($_SESSION['id_adm'])) {
    audit_log($bdd, 'admin.deconnexion', 'admin', (string) $_SESSION['id_adm']);
}
$_SESSION=array();
session_destroy();
//header("Location:?pages=login");
?>
<center><a class="btn btn-danger" href="?pages=login">Deconnectez-vous</a></center>