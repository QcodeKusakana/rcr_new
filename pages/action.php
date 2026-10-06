<?php
if(isset($_GET['id']) AND $_GET['id']>0) {
    $getid = intval($_GET['id']);

    if(isset($_GET['t'],$_GET['id']) AND !empty($_GET['t'])AND !empty($_GET['id'])){
        $getid=(int) $_GET['id'];
        $gett=(int) $_GET['t'];

        $ip=$_SERVER['REMOTE_ADDR'];

        $chek=$bdd->prepare('SELECT id_act FROM activite WHERE id_act=?');
        $chek->execute(array($getid));
//SUPPRESSION SI USER A DEJA COCHEZ J'AIME OU jE M'AIME PAS, TABLE LIKES
        if($chek->rowCount()==1){
            if($gett==1){
                $chek_like=$bdd->prepare('SELECT * FROM likes WHERE id_act=? AND ip=?');
                $chek_like->execute(array($getid,$ip));

                $del=$bdd->prepare('DELETE FROM dislike WHERE id_act=? AND ip=?');
                $del->execute(array($getid,$ip));

                if($chek_like->rowCount()==1){
                    $del=$bdd->prepare('DELETE FROM likes WHERE id_act=? AND ip=?');
                    $del->execute(array($getid,$ip));
                    header("location:?pages=detail&categ=".$_GET['categ']."&id=".$_GET['id']);
                }else{
                    $ins=$bdd->prepare('INSERT INTO likes(id_act,ip) VALUES (?,?)');
                    $ins->execute(array($getid,$ip));
                    header("location:?pages=detail&categ=".$_GET['categ']."&id=".$_GET['id']);

                }

            }
//SUPPRESSION SI USER A DEJA COCHEZ J'AIME OU jE M'AIME PAS, TABLE DISLIKES
            elseif ($gett==2){
                $chek_like=$bdd->prepare('SELECT * FROM dislike WHERE id_act=? AND ip=?');
                $chek_like->execute(array($getid,$ip));

                $del=$bdd->prepare('DELETE FROM likes WHERE id_act=? AND ip=?');
                $del->execute(array($getid,$ip));

                if($chek_like->rowCount()==1){
                    $del=$bdd->prepare('DELETE FROM dislike WHERE id_act=? AND ip=?');
                    $del->execute(array($getid,$ip));
                    header("location:?pages=detail&categ=".$_GET['categ']."&id=".$_GET['id']);
                }else{
                    $ins=$bdd->prepare('INSERT INTO dislike(id_act,ip) VALUES (?,?)');
                    $ins->execute(array($getid,$ip));
                    header("location:?pages=detail&categ=".$_GET['categ']."&id=".$_GET['id']);
                }
            }
            //header('location:?p=publications&id='.$getid);
        }
    }
}

