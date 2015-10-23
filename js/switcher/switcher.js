function dataTablesExec() {
//	$j('#container').css('width', '99%');

	var datatable = $j('#table_users').dataTable( {
//		'sScrollX': '100%',
                'bLengthChange': false,
		//'bScrollCollapse': true,
//		'iDisplayLength': 50,
                "bFilter": true,
                "bInfo": false,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
 
                'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 3 ] } ],

                'aoColumns': [
                                { "sType": "numeric" },
                                null,
                                null,
                                null,
                                null
                            ],
         
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
        
	var datatable = $j('#table_users_for_service').dataTable( {

                 "bJQueryUI": true,
                'bLengthChange': true,
		//'bScrollCollapse': true,
//		'iDisplayLength': 10,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
 
                'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 4 ] } ],

                'aoColumns': [
                                null,
                                null,
                                { 'sType': "date-eu" },
                                null,
                                null
                            ],
         
//                'bPaginate': false

                'bPaginate': true,
                "aaSorting": [[ 2, "desc" ]],
                
                "oLanguage": 
                 {
                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                 },
                 "fnDrawCallback":
                    function () {
                        // put the sort icon outside of the DataTables_sort_wrapper div
                        // for better display styling with CSS
                        $j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
                                sortIcon = $j(this).find('span').clone();
                                $j(this).find('span').remove();
                                $j(this).parents('th').append(sortIcon);
                                });
                    } 
        
//		'sPaginationType': 'full_numbers'
	}).show();

    var datatable = $j('#sortable_S').dataTable( {
//		'sScrollX': '100%',
                'bLengthChange': false,
		//'bScrollCollapse': true,
//		'iDisplayLength': 50,
                "bFilter": true,
                "bInfo": false,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
 
//                'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 3 ] } ],

                'aoColumns': [
//                                { "sType": "numeric" },
                                { 'sType': "date-euro" },
                                null,
                                null,
                                { 'sType': "date-euro" }
                            ],
         
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
        
}

function initListLservices() {
	$j('table.sortable').dataTable( {		
        "bLengthChange" : false,
		"bFilter" : true,
		"bInfo" : false,
		"bSort" : true,
		"bAutoWidth" : true,
		"bDeferRender" : true,
		'aoColumnDefs' : [ {
			"bSortable" : false,
			"aTargets" : [ 5 ]
		} ],
		"aoColumns" : [ null, null, null, null, null, null ],
		"bPaginate" : false
	}).show();	
}
