<?php
if (isset($_SESSION['_ov_t'], $_SESSION['_ov_n']) && (time() - (int) $_SESSION['_ov_t']) < 30) {
    $user_nbr_visit = (int) $_SESSION['_ov_n']; // dernière valeur connue : évite 5 requêtes SQL à chaque page
} else {
    $temps_session=60;
    $temps_actuel=date("U");
    $ip_user=$_SERVER['REMOTE_ADDR'];

    $req_ip_exist=$bdd->prepare('SELECT * FROM horsline WHERE ip_user=?');
    $req_ip_exist->execute(array($ip_user));
    $ip_existe=$req_ip_exist->rowCount();

    if($ip_existe==0){
        $add_ip=$bdd->prepare('INSERT INTO horsline(ip_user,temps) VALUES(?,?)');
        $add_ip->execute(array($ip_user,$temps_actuel));
    }else{
        $udate_ip=$bdd->prepare('UPDATE horsline SET temps=? where ip_user=?');
        $udate_ip->execute(array($temps_actuel,$ip_user));
    }

    $session_delete_time=$temps_actuel-$temps_session;
    $del_id=$bdd->prepare('DELETE FROM horsline WHERE temps < ?');
    $del_id->execute(array($session_delete_time));

    $user_nbr_visit=(int) $bdd->query('SELECT COUNT(*) FROM horsline')->fetchColumn();
    $_SESSION['_ov_t'] = time();
    $_SESSION['_ov_n'] = $user_nbr_visit;
}

?>