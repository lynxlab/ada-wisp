function initDoc() {
	$j(document).ready(function() {
		var appointmentsTables = ["sortable_A","sortable_S","sortable_event_proposed"];
	
		for (var i=0; i < appointmentsTables.length; i++ ) {
			$j('.'+appointmentsTables[i]).dataTable( {
				"bLengthChange": false,
				"bFilter":       false,
				"bInfo":         false,
				"bPaginate":     false,
				"bSort":         true,
				"bAutoWidth":    true,
				"bDeferRender":  true,
                "oLanguage": {
                	"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                },				
				"aoColumns": [
				              { "sType": "date-euro" },
				              null,
				              null
				             ]
			}).show();
		}
		
		$j(".sortable").dataTable( {
			"bLengthChange": false,
			"bFilter":       true,
			"bInfo":         false,
			'bPaginate':     false,
			"bSort":         true,
			"bAutoWidth":    true,
			"bDeferRender":  true,
            "oLanguage": {
            	"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
            },			
			"aoColumnDefs": [
			                 { "bSortable": false,
			                	"aTargets": [ 1 ] } 
			                ],
			"aoColumns": [
			              null,
			              null,
			              null,
			              null,
			              { 'sType': "date-eu" }
			             ]
		}).show();
		
		$j("#table_preassigned_students").dataTable( {
			"bLengthChange": false,
			"bFilter":       true,
			"bInfo":         false,
			'bPaginate':     true,
			"bSort":         true,
			"bAutoWidth":    true,
            "oLanguage": {
            	"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
            },			
			"aoColumnDefs": [
			                 { "bSortable": false,
			                	"aTargets": [ 3 ] } 
			                ],
			"aoColumns": [
			              null,
			              null,
			              { 'sType': "date-eu" },
			              null
			             ]
		}).show();

// $j("#content_blocco_uno").addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
	$j('[id^="content_blocco_"]').addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")	
	  .find("h3")
	    .addClass("ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ui-corner-bottom")
	    .hover(function() { $j(this).toggleClass("ui-state-hover"); })
	    .prepend('<span class="ui-icon ui-icon-triangle-1-s"></span>')
	    .click(function() {
	      $j(this)
	        .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
	        .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
	        .next().toggleClass("ui-accordion-content-active").slideToggle();
	      return false;
	    })
	    .next()
	      .addClass("ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom")
	      .show();
	
//$j("#content_blocco_due").addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
//  .find("h3")
//    .addClass("ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ui-corner-bottom")
//    .hover(function() { $j(this).toggleClass("ui-state-hover"); })
//    .prepend('<span class="ui-icon ui-icon-triangle-1-s"></span>')
//    .click(function() {
//      $j(this)
//        .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
//        .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
//        .next().toggleClass("ui-accordion-content-active").slideToggle();
//      return false;
//    })
//    .next()
//      .addClass("ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom")
//      .show();

	});
}
var appWindow;
function sendEventProposal (userID) {
	$j.when(getHelpServiceID()).done(function(helpServiceID) {
		appWindow.location = HTTP_ROOT_DIR +
		'/comunica/send_event_proposal.php?id_user='+ userID +
		'&id_course='+parseInt(helpServiceID);
	});
}

function getHelpServiceID() {
	var d = $j.Deferred();
	
	if ($j('#helpServiceID').length>0) d.resolve(parseInt($j('#helpServiceID').val()));
	else {
		// prepare and show the dialog to select a helpServiceID
		var dialog_buttons = {};
		
		dialog_buttons[i18n['confirm']] = function() {			
			var helpServiceID = parseInt($j('#selectHelpService option:selected').val());
			if (helpServiceID>0) {
				/**
				 * window must be opened here, and then its location set
				 * because here it's where the user has clicked and not
				 * inside the ajax.done callback where opening a new window
				 * will trigger the browser popup blocker.
				 */	
				appWindow = openMessenger('',800,600);
				d.resolve(helpServiceID);
			}
			$j(this).dialog('close');
			
		};
		
		dialog_buttons[i18n['cancel']] = function() {
			var firstVal = $j('#selectHelpService option:first').val();
			$j('#selectHelpService [value='+firstVal+']').attr('selected','selected');
			$j('#selectHelpService').val(firstVal);
			d.reject();
			$j(this).dialog('close');
		};
		
		$j('#selectServiceDialog').dialog({
			show: {
				effect: "fade",
				duration: 200
			},
			draggable: false,
			resizable: false,
			autoOpen: true,
			width: 400,
			modal: true,
			buttons: dialog_buttons,
			close: dialog_buttons[i18n['cancel']]
		});
	}
	return d.promise();
}