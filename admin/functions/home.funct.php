<?php
if (isset($_GET['supprimer'],$_GET['supprimer'])){
    $supprim_id=htmlspecialchars($_GET['supprimer']);

    $supprimer=$bdd->prepare("DELETE FROM activite WHERE photo=?");
    $supprimer->execute(array($supprim_id));
    $req=$supprimer->rowCount();
    if($req==1){
        unlink("./../media/images_activ/".$supprim_id);
    }else{
        //header("location:?pages=home");
    }
}
if(isset($_GET['confirmer'])AND !empty($_GET['confirmer']))
{
    $confirmer=(int) $_GET['confirmer'];

    $req=$bdd->prepare('UPDATE activite SET etat_modifier = 1 WHERE id_act =?');
    $req->execute(array($confirmer));

}

//deconfirmation d'inscription
if(isset($_GET['deconfirmer'])AND !empty($_GET['deconfirmer']))
{
    $confirmer=(int) $_GET['deconfirmer'];

    $req=$bdd->prepare('UPDATE activite SET etat_modifier = 0 WHERE id_act =?');
    $req->execute(array($confirmer));

}

if (isset($_GET['id'],$_GET['id'])){

    $supprim_id=htmlspecialchars($_GET['id']);

    $supprimer = $bdd -> prepare("DELETE FROM activite WHERE id_act = ?");
    $supprimer -> execute(array($supprim_id));
}
$perPage=9;
$req=$bdd->query("SELECT COUNT(*) AS total FROM activite");
$result=$req->fetch();
$total=$result['total'];

$nbPage = max(1, (int) ceil($total/$perPage));

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
$les_articles=$bdd->query("SELECT * FROM activite WHERE  etat_depot = 0 ORDER BY date_pub DESC LIMIT  $firstOfPage,$perPage");