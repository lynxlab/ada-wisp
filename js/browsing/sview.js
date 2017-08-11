/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc() {
    initRight();
    initForum();
    initMessages();
    $j("select, input, a.button, button, textarea").uniform();
}     
      
function initRight() {
$j("#blocco_due").addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
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
}      



function initForum() {
    $j(".courseNodeView").addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
  .find("h3")
    .addClass("ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ui-corner-bottom")
    .hover(function() { $j(this).toggleClass("ui-state-hover"); })
    .prepend('<span class="ui-icon ui-icon-triangle-1-s"></span>')
    .click(function() {
      $j(this)
        .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
        .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
        .next().toggleClass("ui-accordion-content-active").slideToggle().end()
        .nextAll('.conversation').toggleClass("ui-accordion-content-active").slideToggle();
/*        .next().toggleClass("ui-accordion-content-active").slideToggle();*/
      return false;
    })
    .next()
      .addClass("ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom")
      .show();

}

function initMessages() {
	var datatable = $j('.sortable_A').dataTable( {
                'bLengthChange': false,
                "bFilter": false,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
		'bDeferRender': true,
                
                'aoColumns': [
                                { 'sType': "date-euro" },
                                null,
                                null,
                                null
                            ],
         
                'bPaginate': true,
                "aaSorting": [[ 0, "desc" ]],
//                "aaSorting": [[ 0, "desc" ]],
                
                "oLanguage": 
                 {
                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                 }

	}).show();

	var datatable = $j('.sortable_S').dataTable( {
                'bLengthChange': false,
                "bFilter": false,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
		'bDeferRender': true,
                "bPaginate" : true,
                
         
                
                'aoColumns': [
                                { 'sType': "date-euro" },
                                null,
                                null
                            ],
         
                "aaSorting": [[ 0, "desc" ]],
                
                "oLanguage": 
                 {
                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                 }

	}).show();
    
}
