<?php
$nbrdocument=$bdd->query('SELECT * FROM equipes');
$nbrFichier=$nbrdocument->rowCount();
if(isset($_POST['save']) && !csrf_verify())
{
    $erreur = "Session expirée, merci de recharger la page et réessayer.";
}
elseif(isset($_POST['save']))
{
    $name=  htmlspecialchars(trim((string) ($_POST['name'] ?? '')));
    $telephone=  htmlspecialchars(trim((string) ($_POST['telephone'] ?? '')));
    $mail=  trim((string) ($_POST['mail'] ?? ''));
    $function=  htmlspecialchars(trim((string) ($_POST['function'] ?? '')));
    $resume=  htmlspecialchars(trim((string) ($_POST['resume'] ?? '')));

    if(!empty($_POST['name']) and !empty($_POST['function']) and !empty($_FILES['photo']['name'])){
        require_once __DIR__ . '/../../includes/upload.php';
        if($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)){
            $erreur="Votre adresse mail n'est pas valide !";
        } else {
            $mailexist = 0;
            if($mail !== ''){
                $reqmail=$bdd->prepare("SELECT COUNT(*) FROM equipes WHERE mail=?");
                $reqmail->execute(array($mail));
                $mailexist=(int) $reqmail->fetchColumn();
            }
            if($mailexist > 0){
                $erreur=" Adresse mail déjà utilisée !";
            } else {
                $finalcateg = upload_enregistrer_image($_FILES['photo'], MEDIA_EQUIPE, 'equipe');
                if($finalcateg === null){
                    $erreur="Image refusée : seuls les fichiers JPG, PNG ou WebP de 5 Mo maximum sont acceptés.";
                } else {
                    // `function` est un mot réservé MySQL : nom de colonne entre accents graves.
                    $insertmbr=$bdd->prepare("INSERT INTO equipes(`name`,`telephone`,`mail`,`function`,`resume`,`photo`) VALUES(?,?,?,?,?,?)");
                    $insertmbr->execute(array($name,$telephone,$mail,$function,$resume,$finalcateg));
                    audit_log($bdd, 'equipe.creer', 'equipes', (string) $bdd->lastInsertId(), $name);
                    $errprs="Enregistrement terminé avec succès !";
                }
            }
        }
    }else{
        $erreur="Veuillez compléter les champs !";
    }
}
?>