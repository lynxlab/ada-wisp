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
				"aoColumns": [
				              { "sType": "date-euro" },
				              null,
				              null
				             ],
	            "oLanguage": 
	            {
	                "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
	            }				             
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
			             ],
            "oLanguage": 
            {
                "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
            }
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