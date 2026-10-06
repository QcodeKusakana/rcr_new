<?php
// ============================================================
// RESTRICTION D'ACCÈS (ajoutée suite à l'audit de sécurité)
// Cette page permet de nommer/rétrograder/valider/supprimer des comptes
// administrateurs : elle ne doit être utilisable que par le niveau le
// plus élevé (7 = Webmaster), à l'image du libellé "Super Administrateur"
// déjà utilisé pour ce niveau dans la vue admin/pages/valider_adm.php.
// Avant cette correction, aucune vérification de session ni de niveau
// n'était effectuée : n'importe quel visiteur pouvait déclencher ces
// actions en connaissant simplement l'URL.
// ============================================================
if (!isset($_SESSION['id_adm']) || (int) ($_SESSION['niveau'] ?? 0) !== 7) {
    return;
}

if (isset($_GET['supprimer'])AND !empty($_GET['supprimer']))
{
    $supprimer=(int) $_GET['supprimer'];
    $req=$bdd->prepare('DELETE FROM admin WHERE id_adm=?');
    $req->execute(array($supprimer));
}
//confirmation d'inscription
if(isset($_GET['confirmer'])AND !empty($_GET['confirmer']))
{
    $confirmer=(int) $_GET['confirmer'];

    $req=$bdd->prepare('UPDATE admin SET confirmer=1 WHERE id_adm=?');
    $req->execute(array($confirmer));
}
//deconfirmation d'inscription
if(isset($_GET['deconfirmer'])AND !empty($_GET['deconfirmer']))
{
    $confirmer=(int) $_GET['deconfirmer'];

    $req=$bdd->prepare('UPDATE admin SET confirmer=0 WHERE id_adm=?');
    $req->execute(array($confirmer));
}
//confirmation d'inscription
//nomme adm
if(isset($_GET['nomme91'])AND !empty($_GET['nomme91']))
{
    $nomme=(int) $_GET['nomme91'];

    $req=$bdd->prepare('UPDATE admin SET niveau=6 WHERE id_adm=?');
    $req->execute(array($nomme));
}
//retirer adm
if(isset($_GET['retirer6'])AND !empty($_GET['retirer6']))
{
    $confirmer=(int) $_GET['retirer6'];

    $req=$bdd->prepare('UPDATE admin SET niveau=91 WHERE id_adm=?');
    $req->execute(array($confirmer));
}


if(isset($_GET['nomme92'])AND !empty($_GET['nomme92']))
{
    $nomme=(int) $_GET['nomme92'];

    $req=$bdd->prepare('UPDATE admin SET niveau=5 WHERE id_adm=?');
    $req->execute(array($nomme));
}
//retirer adm
if(isset($_GET['retirer5'])AND !empty($_GET['retirer5']))
{
    $confirmer=(int) $_GET['retirer5'];

    $req=$bdd->prepare('UPDATE admin SET niveau=92 WHERE id_adm=?');
    $req->execute(array($confirmer));
}

if(isset($_GET['nomme93'])AND !empty($_GET['nomme93']))
{
    $nomme=(int) $_GET['nomme93'];

    $req=$bdd->prepare('UPDATE admin SET niveau=4 WHERE id_adm=?');
    $req->execute(array($nomme));
}
//retirer adm
if(isset($_GET['retirer4'])AND !empty($_GET['retirer4']))
{
    $confirmer=(int) $_GET['retirer4'];

    $req=$bdd->prepare('UPDATE admin SET niveau=93 WHERE id_adm=?');
    $req->execute(array($confirmer));
}


if(isset($_GET['nomme94'])AND !empty($_GET['nomme94']))
{
    $nomme=(int) $_GET['nomme94'];

    $req=$bdd->prepare('UPDATE admin SET niveau=3 WHERE id_adm=?');
    $req->execute(array($nomme));
}
//retirer adm
if(isset($_GET['retirer3'])AND !empty($_GET['retirer3']))
{
    $confirmer=(int) $_GET['retirer3'];

    $req=$bdd->prepare('UPDATE admin SET niveau=94 WHERE id_adm=?');
    $req->execute(array($confirmer));
}


if(isset($_GET['nomme95'])AND !empty($_GET['nomme95']))
{
    $nomme=(int) $_GET['nomme95'];

    $req=$bdd->prepare('UPDATE admin SET niveau=2 WHERE id_adm=?');
    $req->execute(array($nomme));
}
//retirer adm
if(isset($_GET['retirer2'])AND !empty($_GET['retirer2']))
{
    $confirmer=(int) $_GET['retirer2'];

    $req=$bdd->prepare('UPDATE admin SET niveau=95 WHERE id_adm=?');
    $req->execute(array($confirmer));
}
if(isset($_GET['retirer9'])AND !empty($_GET['retirer9']))
{
    $confirmer=(int) $_GET['retirer9'];

    $req=$bdd->prepare('UPDATE admin SET niveau=96 WHERE id_adm=?');
    $req->execute(array($confirmer));
}
//deconfirmation d'inscription
if(isset($_POST['nomme']) && !csrf_verify()){

    $erreur = "Session expirée, merci de recharger la page et réessayer.";

} elseif(isset($_POST['nomme'])){

    $pseudo =  htmlspecialchars($_POST['pseudo']);
    $fonction = htmlspecialchars($_POST['fonction']);

    if(!empty($pseudo)AND !empty($fonction)){

        $requser = $bdd->prepare("SELECT * FROM admin WHERE pseudo=?");
        $requser->execute(array($pseudo));
        $userexist = $requser->rowCount();

        if($userexist == 1){

            $foncts = $bdd->prepare("UPDATE admin SET fonction=? WHERE pseudo=?");
            $foncts->execute(array($fonction,$pseudo));

            if($fonction == "CP"){

                $fonct=$bdd->query("UPDATE admin SET niveau=6 WHERE fonction='CP'");
            }
            elseif($fonction == "SG"){

                $fonct = $bdd->query("UPDATE admin SET niveau=5 WHERE fonction='SG'");
            }
            elseif($fonction == "CE"){

                $fonct=$bdd->query("UPDATE admin SET niveau=4 WHERE fonction='CE'");
            }
            elseif($fonction == "RG"){
                $fonct = $bdd->query("UPDATE admin SET niveau=3, confirmer=1 WHERE fonction='RG'");
            }
            elseif($fonction == "TG"){
                $fonct = $bdd->query("UPDATE admin SET niveau=2, confirmer=1 WHERE fonction='TG'");
            }
            elseif($fonction == "WM"){
                $fonct = $bdd->query("UPDATE admin SET niveau=7, confirmer=1 WHERE fonction='WM'");
            }
            elseif($fonction == "autre"){

                $fonct = $bdd->query("UPDATE admin SET niveau=9 WHERE fonction='autre'");
            }
            echo 'bien ajouter';
        }else {
            $erreur="Le Pseudo introuvable!";
        }
    }  else {
        $erreur="Tous les champs doivent etre completer";
    }
}

$administrateur=$bdd->query("SELECT * FROM admin ORDER BY pseudo ASC");
//function


































