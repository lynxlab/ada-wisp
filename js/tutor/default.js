document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");

function initDoc()
{
	$j(document).ready(function() {
		if ($j().uniform) $j("select, input, a.button, button, textarea").uniform();
		
		if ($j('#userSummary').length > 0)
		{
			$j('#userSummary').dataTable( {		
		        "bLengthChange" : false,
				"bFilter" : false,
				"bInfo" : false,
				"bSort" : true,
				"bAutoWidth" : true,
				"bDeferRender" : true,
				'aoColumnDefs' : [ {
					"bSortable" : false,
					"aTargets" : [ 0 ]
				} ],
				"aoColumns" : [ null, null, { 'sType': "date-eu" }, null ],
				"bPaginate" : false
			}).show();			
		}
	});	
}

function Pager(page) {
}

Pager.prototype.showPage = function(page) {
    if($(page)) {
        var pagedElement = 'pe_' + page;

        $(page).siblings().each(this.hidePageElement);

        $(page).addClassName('selectedPage');
        $(pagedElement).show();
    }
}

Pager.prototype.hidePageElement = function(pageElement) {
   var pagedElement = 'pe_' + pageElement.identify();
    if($(pagedElement) && $(pagedElement).visible()) {
        $(pagedElement).hide();
        $(pageElement).removeClassName('selectedPage');
    }

}

var PAGER = new Pager();