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
 
//                'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 3 ] } ],

                'aoColumns': [
//                                { "sType": "numeric" },
                                null,
                                null,
                                { 'sType': "date-eu" },
                                null
                            ],
         
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
}

/*
        $(document).ready(function() {
                $('#listaImmobili').dataTable({
                "bPaginate": false,
                "bLengthChange": false,
                "bFilter": true,
                "bSort": true,
                "bInfo": false,
                "bAutoWidth": true,
                "aaSorting": [[ 2, "desc" ],[ 5, "asc" ]]
            } );
        } );
*/