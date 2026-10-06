<?php
//include_once('./functions/main_function.php');
$reqArticle=$bdd->query("SELECT * FROM activite WHERE  etat_modifier=1 ORDER BY date_pub DESC LIMIT 0,2");

$reqequipe=$bdd->query('SELECT * FROM equipes WHERE  valid=0');
// Équipe affichée sur l'accueil (même filtre que la page « Notre équipe ») : tableau, ordre de saisie, 12 au plus
$equipeHome = $bdd->query('SELECT id_eq, `name`, `function`, resume, photo, mail, telephone FROM equipes WHERE valid = 0 ORDER BY id_eq ASC LIMIT 12')->fetchAll(PDO::FETCH_ASSOC);

// 🎨 CORRECTIF DESIGN : la page d'accueil affichait une section "Nos
// clients" avec 6 logos statiques (assets/img/clients/client-1.png...),
// un reliquat de gabarit générique sans aucun sens pour un parti
// politique. La table `partenaire` existe déjà et alimente
// pages/partenaire.php avec de vrais partenaires — même requête
// reprise ici pour afficher les mêmes données réelles sur l'accueil.
$reqPartenairesHome = $bdd->query("SELECT * FROM partenaire ORDER BY id_part DESC LIMIT 6");
// Partenaires de l'accueil (tableau, 24 au plus) : seuls ceux dont le logo existe réellement sont affichés
$partenairesHome = array_values(array_filter(
    $bdd->query("SELECT id_part, nom_part, photo FROM partenaire ORDER BY id_part DESC LIMIT 24")->fetchAll(PDO::FETCH_ASSOC),
    fn($p) => $p['photo'] !== '' && is_file(__DIR__ . '/../media/images_part/' . basename((string) $p['photo']))
));

// 🎨 CORRECTIF DESIGN : la section "Galerie" affichait 6 photos
// génériques (images/company/13.jpg...) avec des légendes inventées
// ("Réunion officielle", "Conférence"...) sans rapport avec un
// événement réel. Remplacée par les vraies photos des activités déjà
// publiées (même table que la section "Événements récents" ci-dessous).
$reqGalerieHome = $bdd->query("
    SELECT id_act, photo, titre, categorie
    FROM activite
    WHERE etat_modifier = 1 AND photo IS NOT NULL AND photo <> ''
    ORDER BY date_pub DESC
    LIMIT 8
");