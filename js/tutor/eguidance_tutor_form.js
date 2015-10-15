function toggleVisiblePersonal(select)
{
    var status = select.value;
    var idUser = $j("#id_utente").val();
    var idInstance = $j("#id_istanza_corso").val();
    
    alert (status);
           
//    var data = {
//        'status' : select.value,
//        'id_user': idUser,
//        'id_instance': idInstance
//    }
//     $j.ajax({
//       type	: 'POST',
//       url	: HTTP_ROOT_DIR+ '/switcher/ajax/updateServiceStatus.php',
//       data	: data,
//       dataType :'json'
//       })
//       .done   (function( JSONObj )
//       {
//           showHideDiv(JSONObj.title,JSONObj.msg);
//       })
//       .fail   (function() { 
//            console.log("ajax call has failed"); 
//	} );
    
}

function showHideDiv ( title, message)
{
    var theDiv = $j("<div id='ADAJAX' class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
    theDiv.css("position","fixed");
    theDiv.css("width", "350px");
    theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
    theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));	
    theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() { 
    theDiv.remove(); 
    if (typeof reload != 'undefined' && reload) self.location.reload(true); });
}