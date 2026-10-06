$(function(){
    $('.btnChat').click(function(){
        var $icoTarget, $icoNext ;
        if($(this).is('.show')){
            hideZoneChat() ;
            hideZoneMembres() ;
            $(this).removeClass('show') ;
            $icoTarget = $(this).find('.ico2') ;
            $icoNext = $(this).find('.ico1') ;
        }else{
            $(this).addClass('show') ;
            showZoneMembres() ;
            $icoTarget = $(this).find('.ico1') ;
            $icoNext = $(this).find('.ico2') ;
        }

        $icoTarget.animate({
            opacity : 0  , top : '20px'
        }, 400,'easeInExpo') ;
        setTimeout(() => {
            $icoNext.animate({
                opacity : 1  , top : '0px'
            }, 400,'easeOutExpo') ;
        }, 400);    
    })

    $('.zoneMembres').delegate('.membres .item' , 'click' ,function(e){
        e.preventDefault() ;
        var idUser = $(this).data('iduser') ;
        loadZoneMembres(idUser) ;
    })
    $('.zoneChat').delegate('.entete .closeChat', 'click' ,function(){
        hideZoneChat() ;
    })
    
    //Envoie du message

    $('.zoneChat').delegate('.newMessage form :submit', 'click' ,function(e){
        e.preventDefault() ;
        var $targetForm =  $(this).parents('form') ;
        var destinateur = $(this).parents('.zoneChat').eq(0).find('.entete p.name').data('iduser') ;
        var message =  $targetForm.find('textarea.message').val() ;

        $.post('page/post--envoieMessage.php',{
            message : message ,
            destinateur : destinateur
        }, function(data){
            if(data.trim() != ""){
                alert(data) ;
            }else{
                $targetForm.find('textarea.message').val('') ;
                //on actualise la zone de chat
                // $('.zoneChat .messages').load('page/chatZoneMessage.php', {
                //     idUser : destinateur
                // },function(){
                //     $('.zoneChat .messages')[0].scrollTop = $('.zoneChat .messages')[0].scrollHeight ;
                // })
                func_actualizeZoneChatNew() ;
            }
        })
    })
    function loadZoneMembres(idUser){
        $('.zoneChat').load("page/chatZone.php", {
            idUser : idUser 
        }, function(){
            showZoneChat() ;
            $('.zoneChat .messages')[0].scrollTop = $('.zoneChat .messages')[0].scrollHeight ;
        })
    }
    function showZoneMembres(){
        $('.zoneMembres').css('display','block') ;
        $('.zoneMembres').animate({
            opacity : 1  , left : '20px'
        }, 400,'easeOutExpo') ;
        $('.zoneMembres').addClass('show') ; 
    }
    function showZoneChat(){
        $('.zoneChat').css('display','block') ;
        $('.zoneChat').animate({
            opacity : 1  , left : '322px'
        }, 400,'easeOutExpo') ;
        $('.zoneChat').addClass('show') ; 
    }
    function hideZoneMembres(){
        $('.zoneMembres').animate({
            opacity : 0  , left : '80px'
        }, 400,'easeInExpo',function(){
            $('.zoneMembres').css('display','none') ;
            $('.zoneMembres').removeClass('show') ; 
        }) ;
    }
    function hideZoneChat(){
        $('.zoneChat').animate({
            opacity : 0  , left : '400px'
        }, 400,'easeOutExpo',function(){
            $('.zoneChat').css('display','none') ;
            $('.zoneChat').removeClass('show') ; 
        }) ;
    }
    $('.zoneMembres').delegate('a.linkPagination' , 'click' ,function(e){
        clearTimeout(actualizeZoneChatTimer) ;
    })
    
}) ;

func_actualizeZoneChat = function (){
    if(dataMembreLoaded && dataNbreMessageLoaded){
        dataMembreLoaded = false ;
        dataNbreMessageLoaded = false ;
        var indexListeMembre = $('.zoneMembres a.linkPagination').eq(0).data('indextargetlistemembre') ;
        $('.zone--data.membres').load("page/chatListMembres.php", "indexlistemembre=" + indexListeMembre , function(){
            dataMembreLoaded = true ;
        }) ;
        $('.nbreMessage').load("page/nbreMessage.php",function(){
            dataNbreMessageLoaded = true ;
        }) ;
    }
    actualizeZoneChatTimer = setTimeout(() => {
        func_actualizeZoneChat() ; 
    }, 5000);
}
func_actualizeZoneChat() ;

actualizeZoneChatNewLoaded = true ;
func_actualizeZoneChatNew = function (){
    if(actualizeZoneChatNewLoaded){
        actualizeZoneChatNewLoaded = false ;
        var destinateur = $('.zoneChat').eq(0).find('.entete p.name').data('iduser') ;
        $.post("page/chatZoneMessageNew.php",{
            idUser : destinateur 
        },function(data){
            $('.zoneChat .messages .content').append(data) ;     
            actualizeZoneChatNewLoaded = true ;
        })
    }

    actualizeZoneChatTimer = setTimeout(() => {
        func_actualizeZoneChatNew() ; 
    }, 1000);
}
func_actualizeZoneChatNew() ;

