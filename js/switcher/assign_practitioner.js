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
                $( this ).dialog( "close" );
            }
        }
    });
    
}