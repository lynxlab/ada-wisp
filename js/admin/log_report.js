function initDoc() {
	var datatable = $j('#table_log_report').dataTable( {
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
                                null,
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                                { "sType": "numeric" },
                            ],
         
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
}        
