<br/>
<br/>
<br/>
<br/>
<br/>
<?php
if(isset($_SESSION['id_ad'])){
    // 🔐 Requêtes préparées (corrigé lors de l'audit : la valeur était
    // interpolée directement dans le SQL. Le risque réel était limité ici
    // car $_SESSION['id_ad'] n'est jamais fourni par le visiteur, mais on
    // harmonise avec le reste du projet par précaution/cohérence).
    $l_adherers=$bdd->prepare("SELECT * FROM adhesion
      INNER JOIN provinces ON adhesion.province=provinces.id_p
      INNER JOIN territoires ON adhesion.territoire=territoires.id_tr
      INNER JOIN secteurs ON adhesion.secteur=secteurs.id_sec
      INNER JOIN qualites ON adhesion.id_qt=qualites.id_qt
      INNER JOIN grades ON adhesion.grade=grades.id_gd
      INNER JOIN cotisation ON adhesion.reglement=cotisation.id_cot
      INNER JOIN payments ON adhesion.codes=payments.codes_ad WHERE adhesion.id_ad=?");
    $l_adherers->execute([$_SESSION['id_ad']]);
    $infoAd=$l_adherers->fetch();

    $sponsor=$bdd->prepare("SELECT * FROM adhesion
      INNER JOIN parner ON adhesion.id_ad=parner.id_dest WHERE adhesion.id_ad=?");
    $sponsor->execute([$_SESSION['id_ad']]);
    $rows=$sponsor->rowCount();
    //$spons=$sponsor->fetch();
}else{
    header("Location:?pages=login");
    exit;
} ?>
<!-- ======= Breadcrumbs ======= -->
<!-- End Breadcrumbs -->
<section id="services" class="services"  style="padding: 0px;margin: 0px">
    <div class="container-fluid">

        <div class="section-title">
            <p style="background-color: #202020">
                <img loading="lazy" decoding="async" src="./adhere/money/Tick%20Box_48px.png" style="width: 25px;" alt=""/> <b style="color:#34ce57; font-size: 20px">
                    Merci, nous avons reçu votre demande d’adhésion avec succès. <br/> Mais vous allez payer d'abord le frais d'adhésion pour valider votre demande d'adhésion.
                    <br/><br/>
                   <b style="font-size: 30px"> Vous allez payer : <?= $infoAd['prix'] ?><span> $</span></b>
                </b>

            </p>
        </div>

        <div class="row">
            <div class="col-lg-7 col-md-12 icon-box" style="box-shadow: 2px 2px 8px; background-color: lightcoral;color: #fff;font-size: 19px">
                <div class="icon"><img loading="lazy" decoding="async" src="./images/mpesa.png" width="110px" alt=""/></div>
                <h4 class="title"><a href="">PAIEMENT PAR M-PESA(Compte Marchand)</a></h4>

                <p class="description">
                <h5></h5>
                NUMERO CAISSE :
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">08</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">00</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">0</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">4</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">0</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">9</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">8</span>
                <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-radius: 6px">2</span>
                <br/><br/><h5>Procédure du paiement</h5>
                <hr/>
                <div class="col-lg-6" style="float: left; border-right: 1px solid; ">
                    <span style="float: left;"><span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">1.</span> Composez *1122#</span><br/>
                    <span style="float: left;"><span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">2.</span> Sélectionner la Devise USD ou CDF</span>
                    <span style="float: left;"><span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">3.</span> Entrer 5,(Mes Paiement)</span>
                    <span style="float: left;"><span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">4.</span> Entrer 2, Achats Produits</span>
                    <span style="float: left;"><span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">5.</span> Entrer numéro Caisse:</span>
                </div>
                <div class="col-lg-6" style="float: right; padding-left: 6px">
                    <span style="float: left;"> <span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">6.</span> Entrez Montant:</span><br/>
                    <span style="float: left;"> <span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">7.</span> Raison de la transaction</span>
                    <span style="float: left;"> <span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">8.</span> Insérer votre PIN Mpesa</span>
                    <span style="float: left;"> <span style="background-color: #34ce57;color: #fff;padding:4px;font-weight: bold;border-radius: 50%">9.</span> Confirmez la transaction</span>
                </div>
                </p>
            </div>
            <div class="col-lg-5 col-md-12 icon-box" style="box-shadow: 2px 2px 8px;background-color: #fff;color: #000;font-size: 19px">
                <div class="icon"><img loading="lazy" decoding="async" src="./images/equity.jpg" width="80px" alt=""/></div>
                <h4 class="title"><a href="">PAIEMENT PAR EQUITY BCDC</a></h4>
                Mon compte Bancaire <b>(Compte courant CDF)</b> <br/>
                <hr/>
                <p class="description" style="border: 1px solid;background-color: red ">
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">5</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">3</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">3</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">6</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">8</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">9</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">7</span>
                </p>
                Mon compte Bancaire <b>(Compte courant USD)</b> <br/>
                <hr/>
                <p class="description" style="border: 1px solid;background-color: #ffff00 ">
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">5</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">3</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">3</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">0</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">1</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">2</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">6</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">8</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">5</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">-</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">3</span>
                    <span style="background-color: #fff;color: #000000;padding:4px;font-weight: bold;border-left: 1px solid">9</span>
                </p>
                <div>
                    <?php if(isset($_GET['pages'])=='accuse') { ?>
                        <div style="width: 100%;padding: 10px; text-align: center;border: 0px solid darkseagreen;">

                            <br/> <hr/><a target="_blank" href="./admin/pages/print/print_adherer.php?cod=<?=$_SESSION['id_ad']?>"><b style="color:#38a1f3;"><i class="bi bi-download" style="color: red"></i> Télécharger votre fiche d'adhesion </b></a>
                            <br/> <hr/><a target="_blank" href="index.php?pages=esp_membre"><b style="color:#38a1f3;"> Cliquez-ici pour voir votre espace membre </b></a>
                        </div>
                    <?php } ?>
                </div>
            </div>


        </div>

    </div>
</section><!-- End Services Section -->