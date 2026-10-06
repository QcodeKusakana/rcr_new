<?php
    if(isset($_POST['categorie']) && !csrf_verify()){
        $erreurs = "Session expirée, merci de recharger la page et réessayer.";
    } elseif(isset($_POST['categorie'])){
        $nom_cat=  htmlspecialchars($_POST['nom_cat']);
        $resume=  htmlspecialchars($_POST['resume']);
        if(!empty($nom_cat) and !empty($resume)){
            $reqcat=$bdd->prepare("SELECT * FROM categorie WHERE nom_cat=?");
            $reqcat->execute(array($nom_cat));
            $catexist=$reqcat->rowCount();
            if($catexist==0){
                $req=$bdd->prepare('INSERT INTO categorie(nom_cat,resume) VALUES(?,?)');
                $req->execute(array($nom_cat,$resume));
                $smss="Catégorie ajouter avec succès !";
                unset($nom_cat);
            }  else {
                $erreurs="Catégorie Entré Existe déjà !";
            }
        }else {
            $erreurs="Veuillez compléter les champs !";
        }
    }
?>