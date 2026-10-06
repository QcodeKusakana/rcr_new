<?php
if(isset($_POST['scategorie']) && !csrf_verify()){
    $erreurs = "Session expirée, merci de recharger la page et réessayer.";
} elseif(isset($_POST['scategorie'])){
    $nom=  htmlspecialchars($_POST['nom']);
    $id_cat=  htmlspecialchars($_POST['id_cat']);
    if(!empty($nom) and!empty($id_cat)){
        $reqcat=$bdd->prepare("SELECT * FROM sous_categorie WHERE nom=?");
        $reqcat->execute(array($nom));
        $catexist=$reqcat->rowCount();
        if($catexist==0){
            $req=$bdd->prepare('INSERT INTO sous_categorie(nom,id_cat) VALUES(?,?)');
            $req->execute(array($nom, $id_cat));
            $smss="Sous Catégorie ajouter avec succès !";
            unset($nom);
        }  else {
            $erreurs="Sous Catégorie Entré Existe déjà !";
        }
    }else {
        $erreurs="Veuillez compléter les champs !";
    }
}
$reqcate=$bdd->query("SELECT * FROM categorie");
//$catexists=$reqcate->rowCount();
?>