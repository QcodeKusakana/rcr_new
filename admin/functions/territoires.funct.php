<?php
if (isset($_GET['sup'],$_GET['sup'])){

    $supprim_id=htmlspecialchars($_GET['sup']);

    $supprimer = $bdd -> prepare("DELETE FROM territoires WHERE id_tr = ?");
    $supprimer -> execute(array($supprim_id));
}
if(isset($_GET['tr_id'],$_GET['tr_id'])){
    $tr_id= (int) $_GET['tr_id'];
    $req=$bdd->prepare("UPDATE territoires SET changer = 1 WHERE id_tr=?");
    $req->execute(array($tr_id));
}
if(isset($_GET['delttr_id'],$_GET['delttr_id'])){
    $delttr_id= (int) $_GET['delttr_id'];
    $req=$bdd->prepare("UPDATE territoires SET changer = 0 WHERE id_tr=?");
    $req->execute(array($delttr_id));
    header("Location:?pages=territoires");
}
$perPage=5;
$req=$bdd->query("SELECT COUNT(*) AS total FROM provinces");
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
$les_activite=$bdd->query("SELECT * FROM provinces ORDER BY id_p ASC LIMIT  $firstOfPage,$perPage");




if(isset($_POST['activite']) && !csrf_verify()){

    $erreur = "Session expirée, merci de recharger la page et réessayer.";

} elseif(isset($_POST['activite'])){
    $nom_tr=htmlspecialchars(trim($_POST['nom_tr']));
    $id_p= $_POST['id_p'];
    if(!empty($_POST['nom_tr']) and !empty($_POST['id_p'])){
            $insert=$bdd->prepare("INSERT INTO territoires(nom_tr,id_p) values(?,?)");
            $insert->execute(array($nom_tr,$id_p));
            $sms="Territoire Ajouter avec succès !";
            unset($nom_tr);



    }else{
        $erreur="Veuillez compléter les champs !";
    }
}
$id_prov=$bdd->query('SELECT * FROM provinces');
?>
