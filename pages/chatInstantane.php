
<link rel="stylesheet" href="css/chatInstantane.css">
<script>
    func_actualizeZoneChat = null ;
    actualizeZoneChatTimer = null ;
    dataMembreLoaded  = true ;
    dataNbreMessageLoaded  = true ;
</script>

<div class="chatInstantane">

    <div class="btnChat">
        <div class="b_ico">
          <b style="position: relative; left: 12px;top: 7px;color: #fff"> X</b>
        </div>
        <span style="width: 200px;">Membres en ligne</span>
        <div class="nbreMessage">
            <?php //include("page/nbreMessage.php") ?>
        </div> 
    </div>
    <div class="zoneMembres zone--filter'">
        <div class="zoneSearch form items--filter">
            <?php
            $mbresadherer=$bdd->query('SELECT * FROM adhesion');
            $mbresadherer=$mbresadherer->rowCount();
            ?>
           <p>Membre(s)
               <?php if($user_nbr_mbre >=1){  ?>
               <b style="color: red"> <?= $mbresadherer ?></b>
               <?php }else {
                   ?>
               <?php
               } ?>

           </p>
           <p>
               <strong>Membre(s)</strong>
               <?php if($user_nbr_mbre >=1){  ?>
                   <b style="color: lime"><?= $user_nbr_mbre ?> en ligne </b>
               <?php }else {
                   ?>
               <?php
               } ?>

           </p>
           <p><strong>Visiteur(s)</strong>
               <?php if($user_nbr_visit >=1){  ?>
                   <b style="color: lime"> <?= $user_nbr_visit ?> en ligne </b>
               <?php }else {
                   ?>
                   en ligne
               <?php
               } ?> </p>
           <p>Total en ligne <b style="color: red"> <?= $user_nbr_visit+$user_nbr_mbre ?></b></p>
        </div>
        <div class="membres zone--data sectionPagination">
            <?php //include('page/chatListMembres.php') ?>
        </div>
    </div>
    <div class="zoneChat">
        <?php //include('page/chatZone.php') ?>
    </div>
</div>

<script src="js/chatInstantane.js"></script>
