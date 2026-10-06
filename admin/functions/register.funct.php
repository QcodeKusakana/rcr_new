<?php
// Vérifier si email existe déjà
function mail_taken($mail)
{
    global $bdd;

    $sql = "SELECT id_adm FROM admin WHERE mail = :mail";
    $req = $bdd->prepare($sql);
    $req->execute([
        'mail' => $mail
    ]);

    return count($req->fetchAll());
}

// Vérifier si le pseudo existe déjà (la connexion se fait par pseudo : il doit être unique)
function pseudo_taken($pseudo)
{
    global $bdd;
    $req = $bdd->prepare('SELECT COUNT(*) FROM admin WHERE pseudo = ?');
    $req->execute([$pseudo]);
    return (int) $req->fetchColumn();
}

// Inscription admin (compte NON validé : confirmer = 0, aucun rôle — un administrateur principal doit l'activer)
function insert_register($pseudo, $mail, $password)
{
    global $bdd;

    $sql = "INSERT INTO admin(pseudo, mail, password, confirmer, niveau)
            VALUES(:pseudo, :mail, :password, 0, 0)";

    $req = $bdd->prepare($sql);

    return $req->execute([
        'pseudo' => $pseudo,
        'mail' => $mail,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);
}