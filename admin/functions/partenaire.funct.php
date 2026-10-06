<?php
$nbrdocument=$bdd->query('SELECT * FROM partenaire');
$nbrFichier=$nbrdocument->rowCount();
if(isset($_POST['partenaire']) && !csrf_verify()){
    $erreur = "Session expirée, merci de recharger la page et réessayer.";
} elseif(isset($_POST['partenaire'])){
    $nom_part=htmlspecialchars(trim($_POST['nom_part']));

    if(!empty($_POST['nom_part']) and !empty($_FILES['photo']['name'])){
        require_once __DIR__ . '/../../includes/upload.php';
        $finalpart = upload_enregistrer_image($_FILES['photo'], MEDIA_PARTENAIRES, 'part');
        if($finalpart !== null){
            $insert=$bdd->prepare("INSERT INTO partenaire(nom_part,photo) values(?,?)");
            $insert->execute(array($nom_part,$finalpart));
            audit_log($bdd, 'partenaire.creer', 'partenaire', (string) $bdd->lastInsertId(), $nom_part);
            $sms="Partenaire ajouté avec succès !";
            unset($nom_part,$finalpart);
        }else{
            $erreur="Image refusée : seuls les fichiers JPG, PNG ou WebP de 5 Mo maximum sont acceptés.";
        }
    }else{
        $erreur="Veuillez compléter les champs !";
    }
}
