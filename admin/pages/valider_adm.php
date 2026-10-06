<?php
// Vérification redondante par rapport à admin/functions/valider_adm.funct.php :
// si ce fichier était un jour inclus par un autre chemin, il resterait protégé.
if (!isset($_SESSION['id_adm']) || (int) ($_SESSION['niveau'] ?? 0) !== 7) {
    echo '<div class="alert alert-danger m-4">Accès réservé au Webmaster (niveau 7).</div>';
    return;
}
$adm=$bdd->query('SELECT * FROM admin ORDER BY id_adm DESC ');
?>
<div class="row">
    <div class="col-md-12">
        <h2>Les accès aux administrateurs</h2>
        <hr/>
    </div>
</div>
<div class="row" >
    <div class="col-lg-12" style="background-color: #00407D; color: white;padding: 10px">
        <form action="" method="post">
            <?= csrf_field() ?>
            <div class="col-lg-6">
                <label>Selectionner un administrateur</label>
                <select class="form-control" name="pseudo">
                    <option value="">Selectionner un adm...</option>
                    <?php while ($d=$adm->fetch()){?>
                        <option><?= $d['pseudo']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-lg-6">
                <label for="">Selectionner la fonction</label>
                <select class="form-control" name="fonction">
                    <option>Selectionner...</option>
                    <option value="CP">Chef du parti</option>
                    <option value="SG">Secrétaire Général</option>
                    <option value="CE">Chargé des Effectif</option>
                    <option value="RG">Chargé des Informations</option>
                    <option value="TG">Chargé des Finances</option>
                    <option value="WM">Webmaster</option>
                </select><br/>
                <button style="float: right" class="btn btn-danger" type="submit" name="nomme">Nommer </button>
                <br/><br/>
            </div>
        </form>
    </div>
    <div class="col-md-10">
        <!--   Kitchen Sink -->
        <div class="panel panel-default">
            <div class="panel-heading">
                Les administrateurs du site!
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover datatable-auto">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Pseudo</th>
                            <th>Email</th>
                            <th>Fonction</th>
                            <th>Accès</th>
                            <th>Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($a = $administrateur->fetch()){
                            if($a['mail'] != $_SESSION['mail']){
                                if($a['niveau']!=7){
                                ?>
                                <tr>
                                    <td>2</td>
                                    <td><?= $a['pseudo']?></td>
                                    <td><?= $a['mail']?></td>
                                        <?php
                                        if($a['niveau']==6){
                                        ?>
                                    <td><a style="color:#ff0000" href="?pages=valider_adm&retirer6=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Retirer la fonction de Chef du parti à cet administrateur ?', 'Oui, retirer')">Retirer  Chef du parti</a></td>
                                    <?php
                                    }
                                    elseif($a['niveau']==91){
                                        ?>
                                        <td><a style="color: #01a901" href="?pages=valider_adm&nomme91=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Nommer cet administrateur Chef du parti ?', 'Oui, nommer')">Nommer Chef du parti</a></td>
                                    <?php }
                                    elseif($a['niveau']==5){
                                        ?>
                                        <td><a style="color: #ff0000" href="?pages=valider_adm&retirer5=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Retirer la fonction de Secrétaire Général à cet administrateur ?', 'Oui, retirer')">Retirer Secrétaire Général</a></td>
                                    <?php }
                                    elseif($a['niveau']==92){
                                        ?>
                                        <td><a style="color: #01a901" href="?pages=valider_adm&nomme92=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Nommer cet administrateur Secrétaire Général ?', 'Oui, nommer')">Nommer Secrétaire Général</a></td>
                                    <?php }
                                    elseif($a['niveau']==4){
                                        ?>
                                        <td><a style="color: #ff0000" href="?pages=valider_adm&retirer4=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Retirer la fonction de Chargé des Effectif à cet administrateur ?', 'Oui, retirer')">Retirer Chargé des Effectif</a></td>
                                    <?php }
                                    elseif($a['niveau']==93){
                                        ?>
                                        <td><a style="color: #01a901" href="?pages=valider_adm&nomme93=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Nommer cet administrateur Chargé des Effectif ?', 'Oui, nommer')">Nommer Chargé des Effectif</a></td>
                                    <?php }
                                        elseif($a['niveau']==3){
                                        ?>
                                        <td><a style="color: #ff0000" href="?pages=valider_adm&retirer3=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Retirer la fonction de Chargé des Informations à cet administrateur ?', 'Oui, retirer')">Retirer Chargé des Informations</a></td>
                                    <?php }
                                    elseif($a['niveau']==94){
                                        ?>
                                        <td><a style="color: #01a901" href="?pages=valider_adm&nomme94=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Nommer cet administrateur Chargé des Informations ?', 'Oui, nommer')">Nommer Chargé des Informations</a></td>
                                    <?php }
                                        elseif($a['niveau']==2){
                                        ?>
                                        <td><a style="color: #ff0000" href="?pages=valider_adm&retirer2=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Retirer la fonction de Chargé des Finances à cet administrateur ?', 'Oui, retirer')">Retirer Chargé des Finances</a></td>
                                    <?php }
                                    elseif($a['niveau']==95){
                                        ?>
                                        <td><a style="color: #01a901" href="?pages=valider_adm&nomme95=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Nommer cet administrateur Chargé des Finances ?', 'Oui, nommer')">Nommer Chargé des Finances</a></td>
                                    <?php }
                                    else{
                                        ?>
                                        <td><a style="color: #01a901;background-color: #c1e2b3;padding: 2px;" href="?pages=valider_adm&non=<?= $a['id_adm']?>">Super Administrateur</a></td>
                                    <?php
                                    }
                                    ?>
                                    <td>
                                        <?php
                                        if($a['confirmer']==0){
                                            ?>
                                            <a href="?pages=valider_adm&confirmer=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Valider l\'accès de cet administrateur ?', 'Oui, valider')">Valider</a>
                                        <?php
                                        }
                                        if($a['confirmer']==1){
                                            ?>
                                            <a style="color: #ff0000" href="?pages=valider_adm&deconfirmer=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Invalider l\'accès de cet administrateur ? Il ne pourra plus se connecter.', 'Oui, invalider')">Invalider</a>
                                        <?php } ?>
                                    </td>
                                    <td><a href="?pages=valider_adm&supprimer=<?= $a['id_adm']?>" onclick="return confirmAction(event, 'Supprimer définitivement ce compte administrateur ? Cette action est irréversible.', 'Oui, supprimer')"><i class="icon fa fa-fw fa-trash-o"></i></a></td>
                                </tr>
                            <?php } }} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End  Kitchen Sink -->
    </div>
</div>