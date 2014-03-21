/**
 * change the following to false if you want standard submit
 * instead of ajax 
 */
 var isAjax = true;

function initDoc( startingLabel, nothingFoundLabel )
{
 $j( "#dialog-message" ).dialog({
        modal: true,
        buttons: {
            Ok: function() {
                $j( this ).dialog( "close" );
                self.document.location.href='switcher.php';
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
