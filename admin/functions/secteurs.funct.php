<?php
// Territoire concerné (obligatoire) : sans identifiant valide, retour à la liste des territoires.
$tr_id = (int) ($_GET['tr_id'] ?? ($_POST['id_tr'] ?? 0));
$req = $bdd->prepare("SELECT * FROM territoires WHERE id_tr=?");
$req->execute([$tr_id]);
$infoter = $req->fetch();
if (!$infoter) {
    header("Location:?pages=territoires");
    exit;
}

// Blocage du territoire (plus d'ajout de secteur).
// CORRECTIF : la valeur du paramètre (toujours 1) était utilisée comme identifiant : c'est toujours le
// territoire n°1 qui était bloqué, quel que soit le territoire affiché.
if (isset($_GET['blq'])) {
    $req = $bdd->prepare("UPDATE territoires SET changer = 1 WHERE id_tr=?");
    $req->execute([(int) $infoter['id_tr']]);
    audit_log($bdd, 'territoire.bloquer', 'territoires', (string) $infoter['id_tr']);
    header("Location:?pages=territoires");
    exit;
}

if(isset($_POST['btnsect']) && !csrf_verify()){

    $erreur = "Session expirée, merci de recharger la page et réessayer.";

} elseif(isset($_POST['btnsect'])){
    $nom_sec=htmlspecialchars(trim($_POST['nom_sec']));
    $id_tr= (int) $_POST['id_tr'];
    if(!empty($_POST['nom_sec']) and !empty($_POST['id_tr'])){
        // 🔐 Requête préparée (corrigé lors de l'audit : concaténation directe
        // + comparaison faite sur $tr_id (GET) au lieu de $id_tr (POST), ce
        // qui rendait la détection de doublon non fiable)
        if((int) $infoter['changer'] === 1){ $erreur = "Ce territoire est bloqué : ajout impossible."; }
        $req=$bdd->prepare("SELECT * FROM secteurs WHERE nom_sec=? AND id_tr=?");
        $req->execute([$nom_sec, $id_tr]);
        $countSec=$req->rowCount();
        if(isset($erreur)){
            // territoire bloqué
        }elseif($countSec==0){
            $insert=$bdd->prepare("INSERT INTO secteurs(nom_sec,id_tr) values(?,?)");
            $insert->execute(array($nom_sec,$id_tr));
            $sms="Secteur Ajouter avec succès !";
            unset($nom_sec);
        }else{
            $erreur="Le secteur entrer existe déjà !";
        }
    }else{
        $erreur="Veuillez compléter les champs !";
    }
}


?>
