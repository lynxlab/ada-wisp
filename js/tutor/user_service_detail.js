function saveStatus(select)
{
    var status = select.value;
    var idUser = $j("#id_utente").val();
    var idInstance = $j("#id_istanza_corso").val();
//    alert (status + " " + idUser + " " + idInstance);
    

//    var SelectRow = $j(select).parents('tr')[0];
//    var indexRow=datatable.fnGetPosition($j(select).parents('tr')[0]);
//    var aData = datatable.fnGetData( SelectRow );
//    var idUser=null;
//    var idInstance=null;
//    var indexColumn=null;
//    var re = /\d{1,2}\/{1}\d{1,2}\/{1}\d{2,4}/; 
//    
//    $j.each(aData,function(i,val){
//    	/**
//    	 * if val is a (sort of) date, skip to next iteration
//    	 */    	
//    	if (re.test(val)) {
//    		return;
//    	} else if( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class').indexOf('UserName')!=-1) {
//            idUser=$j(val).attr('id');// text();
//        } else if ( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class')==='id_instance') {
//            idInstance=$j(val).text();
//        } else if ( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class')==='hidden_status') {
//            indexColumn=i;
//        }
//    });
           
    var data = {
        'status' : select.value,
        'id_user': idUser,
        'id_instance': idInstance
    }
     $j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/updateServiceStatus.php',
       data	: data,
       dataType :'json'
       })
       .done   (function( JSONObj )
       {
           showHideDiv(JSONObj.title,JSONObj.msg);
//           var selectedText = $j(select).find('option[value="'+myVal+'"]').text();
//           var cloned = $j(aData[indexColumn]).text(selectedText).clone();
//           datatable.fnUpdate(cloned[0].outerHTML, indexRow,indexColumn, false);
//           
//           $j(select).find('option').each(function(i,e){
//              $j(e).prop('selected', false).removeAttr('selected');
//           });
//           $j(select).val(myVal);
//           $j(select).find('option[value="'+myVal+'"]').prop('selected', true).attr('selected', 'selected');       
//           
//           datatable.fnUpdate($j(select)[0].outerHTML, indexRow, indexColumn+3, false);
//           // console.log($j(select)[0].outerHTML);
//           datatable.fnStandingRedraw();
//           /* if user status is removed  it deletes user row from datatable */
//           if(select.value == 3)  
//           {
//               datatable.fnDeleteRow( SelectRow );
//           }
       })
       .fail   (function() { 
            console.log("ajax call has failed"); 
	} );
    
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