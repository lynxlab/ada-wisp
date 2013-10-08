	/**
	 * handles event click:
	 */
function initDoc() 
{
   var OldActivatedCommonArea = parseInt($j('input[name=common_area]:checked').val());
    $j('form[name=formEditService]').on('submit',function(event) 
        {
            event.preventDefault();
            var ActivatedCommonArea = parseInt($j('input[name=common_area]:checked').val());
            if (ActivatedCommonArea == 0 && OldActivatedCommonArea == 1) {
                jQueryConfirm('#confirmDialog', '#questionDelete', function () {
                    $j('form[name=formEditService]').off('submit');
                    $j('form[name=formEditService]').submit();  
                }, 
                function() { return false; });
            } else {
                $j('form[name=formEditService]').off('submit');
                $j('form[name=formEditService]').submit();  
            }
    }); 
        
            
}

function setModalDialogText (windowId, spanId)
{
	// hides all spans that do not contain a variable
	$j(windowId + ' span').not('[id^="var"]').hide();
	// shows the passed span that holds the message to be shown
	$j(spanId).show();
}

function jQueryConfirm(id, questionId, OKcallback, CancelCallBack)
{
	var okLbl = $j(id + ' .confirmOKLbl').html();
	var cancelLbl = $j(id + ' .confirmCancelLbl').html();

	setModalDialogText(id, questionId);

	$j(id).dialog({
		resizable : false,
		height : 140,
		modal : true,
		buttons : [ {
			text : okLbl,
			click : function() {
				OKcallback();
				$j(this).dialog("close");
			}
		}, {
			text : cancelLbl,
			click : function() {
                                CancelCallBack();
				$j(this).dialog("close");
			}
		} ]
	});
}