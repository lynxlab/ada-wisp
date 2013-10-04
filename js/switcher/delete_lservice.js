function initDoc()
{
 $j( "#dialog-message" ).dialog({
        modal: true,
        buttons: {
            Ok: function() {
                $j( this ).dialog( "close" );
                self.document.location.href='list_lservices.php';
            }
        }
    });
    
}

function showModalDialog ( title, message )
{
	  $j("<p style='text-align:center;'>"+message+"</p>").dialog( {
	    	buttons: { "Ok": function () { $j(this).dialog("close"); } },
	    	close: function (event, ui) { $j(this).remove(); },
	    	resizable: false,
	    	title: title,
	    	modal: true
	  });	
}
