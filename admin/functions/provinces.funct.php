<?php
    if(isset($_POST['prov']) && !csrf_verify()){

        $erreurs = "Session expirée, merci de recharger la page et réessayer.";

    } elseif(isset($_POST['prov'])){
        $nom_p=  htmlspecialchars($_POST['nom_p']);
        if(!empty($nom_p)){
            $reqcat=$bdd->prepare("SELECT * FROM provinces WHERE nom_p=?");
            $reqcat->execute(array($nom_p));
            $catexist=$reqcat->rowCount();
            if($catexist==0){
                $req=$bdd->prepare('INSERT INTO provinces(nom_p) VALUES(?)');
                $req->execute(array($nom_p));
                $smss="Province ajouter avec succès !";
                unset($nom_p);
            }  else {
                $erreurs="Province Entré Existe déjà !";
            }
        }else {
            $erreurs="Veuillez compléter les champs !";
        }
    }
?>