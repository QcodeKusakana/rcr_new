<?php

// Sécurisation de base
if (!isset($_GET["categ"])) {
    die("Catégorie manquante");
}

$categ = htmlspecialchars($_GET["categ"]);

// Dernières activités
$publiactivit = $bdd->prepare("
    SELECT * 
    FROM activite 
    WHERE etat_modifier = 1 
    AND categorie = ? 
    ORDER BY date_pub DESC 
    LIMIT 5
");

$publiactivit->execute([$categ]);

// ⚠️ PROBLÈME ICI : table inexistante
// Solution temporaire propre
$categoriez = []; // au lieu de query cassé

// DETAIL ARTICLE
if (isset($_GET['id']) && !empty($_GET['id'])) {

    $id = (int) $_GET['id'];

    $articles = $bdd->prepare("
        SELECT * 
        FROM activite 
        WHERE id_act = ?
    ");

    $articles->execute([$id]);

    if ($articles->rowCount() == 1) {

        $articles = $articles->fetch();

        $titre = $articles['titre'];
        $description = $articles['description'];
        $categorie = $articles['categorie'];
        $image = $articles['photo'];
        $date_pub = $articles['date_pub'];

        // LIKES
        $likes = $bdd->prepare("SELECT id_like FROM likes WHERE id_act = ?");
        $likes->execute([$id]);
        $likes = $likes->rowCount();

        // DISLIKES
        $dislikes = $bdd->prepare("SELECT id_dislike FROM dislike WHERE id_act = ?");
        $dislikes->execute([$id]);
        $dislikes = $dislikes->rowCount();

        // COMMENTS
        $comments = $bdd->prepare("SELECT id_cmt FROM commentaire WHERE id_act = ?");
        $comments->execute([$id]);
        $comments = $comments->rowCount();

    } else {
        echo "Article introuvable";
    }
}
?>