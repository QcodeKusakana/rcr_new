<?php
if(isset($_GET['confirmer'])AND !empty($_GET['confirmer']))
{
    $confirmer=(int) $_GET['confirmer'];

    $req=$bdd->prepare('UPDATE activite SET etat_modifier = 1 WHERE id_act =?');
    $req->execute(array($confirmer));
    audit_log($bdd, 'activite.publier', 'activite', (string) $confirmer);

}

//deconfirmation d'inscription
if(isset($_GET['deconfirmer'])AND !empty($_GET['deconfirmer']))
{
    $confirmer=(int) $_GET['deconfirmer'];

    $req=$bdd->prepare('UPDATE activite SET etat_modifier = 0 WHERE id_act =?');
    $req->execute(array($confirmer));
    audit_log($bdd, 'activite.depublier', 'activite', (string) $confirmer);

}

$perPage=10;
$req=$bdd->query("SELECT COUNT(*) AS total FROM activite");
$result=$req->fetch();
$total=$result['total'];

$nbPage=max(1, (int) ceil($total/$perPage));

if(isset($_GET['pag']) && !empty($_GET['pag']) && ctype_digit($_GET['pag'])==1){
    if($_GET['pag']>$nbPage){
        $current=$nbPage;
    }else{
        $current=max(1, (int) $_GET['pag']);
    }
}else{
    $current=1;
}

$firstOfPage=($current-1)*$perPage;
$les_activite=$bdd->query("SELECT * FROM activite WHERE  etat_depot = 0 ORDER BY date_pub DESC LIMIT  $firstOfPage,$perPage");



//code pour publier
$nbrdocument=$bdd->query('SELECT * FROM activite');
$nbrFichier=$nbrdocument->rowCount();
if(isset($_POST['activite']) && !csrf_verify()){
    $erreur = "Session expirée, merci de recharger la page et réessayer.";
} elseif(isset($_POST['activite'])){
    $titre=htmlspecialchars(trim($_POST['titre']));
    $description = $_POST['description'];
    $categorie=htmlspecialchars(trim($_POST['categorie']));

    if(!empty($_POST['titre']) and !empty($_POST['description']) and !empty($_POST['categorie'])
    and !empty($_FILES['photo']['name'])){
        require_once __DIR__ . '/../../includes/upload.php';
        $finalcateg = upload_enregistrer_image($_FILES['photo'], MEDIA_ACTIVITES, 'activ');
        if($finalcateg !== null){
            $insert=$bdd->prepare("INSERT INTO activite(titre,description,photo,categorie,id_adm,date_pub) values(?,?,?,?,?,NOW())");
            $insert->execute(array($titre,$description,$finalcateg,$categorie,(int) $_SESSION['id_adm']));
            audit_log($bdd, 'activite.creer', 'activite', (string) $bdd->lastInsertId(), $titre);
            $sms="Activité enregistrée. Elle sera visible sur le site après publication.";
            unset($titre,$description,$finalcateg);
        }else{
            $erreur="Image refusée : seuls les fichiers JPG, PNG ou WebP de 5 Mo maximum sont acceptés.";
        }
    }else{
        $erreur="Veuillez compléter les champs !";
    }
}
$id_categ=$bdd->query('SELECT * FROM categorie');
?>
