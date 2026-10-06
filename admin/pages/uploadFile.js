$(function(){
    $('body').delegate('.libelle-file .pic_file', 'click', function(){
        $(this).parents('.libelle-file').find('input:file').trigger('click') ;
    }) ;
    $('body').delegate('.libelle-file input:file', 'change', function(){
        var xhr = new XMLHttpRequest() ;
        xhr.open('POST' , 'pg/uploadFile.php') ;
        
        var form = document.createElement('form') ;
        var $targetInput = $(this) ;
        var cloneInput = $(this)[0].cloneNode(true) ;
        cloneInput.name = 'monfichier' ;
        form.append(cloneInput) ;

        var formData = new FormData(form) ;
        formData.append('typeFile', $(this).data('typefile')) ;

        var $divParent = $(this).parents('.libelle-file') ;

        xhr.onprogress = function(e) {
            var percent = e.loaded * 100 /  e.total ;
            $divParent.find('.progress').css('width' , percent + '%') ;
        };

        xhr.onreadystatechange = function() { 
            // On gère ici une requête asynchrone
            if (xhr.readyState == 4 && xhr.status == 200) { 
                if(xhr.responseText.trim() == "" || /^success!!!.*/.test(xhr.responseText) ){
                    // Si le fichier est chargé sans erreur
                    var data = xhr.responseText.substring(10)  ;
                    $divParent.find('img.pic_file').attr('src', '../../media/Bdd_images/' + data) ;
                    $divParent.find('#imageProduit').val(data) ;
                    $divParent.find('#imageProduit').trigger('change') ;
                }else{
                    alert('Une erreur est survenue ! \n' + xhr.responseText) ;
                }
            } 
            else if(xhr.readyState == 4 && xhr.status != 200) { 
                // En cas d'erreur !
                alert('Une erreur est survenue !  Code :' + xhr.status + ' \n Texte : ' + xhr.statusText);
            }
            $divParent.find('.progress').css('width' , '0%') ;
        };

        xhr.send(formData) ;
    }) ;
})