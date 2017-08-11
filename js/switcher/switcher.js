function initDoc() {
	dataTablesExec();
	$j('#journey').on('change', 'select#annocorso-select', function() {
		// get base url
		var baseUrl = window.location.href.split("?")[0].replace('#','');
		// get query string as an object
		var queryStringObj = {};
		window.location.search.substring(1).replace(
				new RegExp("([^?=&]+)(=([^&]*))?", "g"),
				function($0, $1, $2, $3) { queryStringObj[$1] = $3; }
		);
		// remove query string unwanted properties
		if ('AA_ISCR_DESC' in queryStringObj) delete queryStringObj.AA_ISCR_DESC;
		if ('ANNO_CORSO' in queryStringObj) delete queryStringObj.ANNO_CORSO;
		// build new query string with what's left in the object
		var newQueryString = $j.param(queryStringObj);
		// append selected value
		if ($j(this).val() !== 'all') {
			newQueryString += ((newQueryString.length == 0) ? '' : '&') + $j(this).val();
		}
		// build new url and redirect
		var newurl = baseUrl + ((newQueryString.length == 0) ? '' : '?') + newQueryString;
		$j('body').css({'opacity': '0.2'});
		window.location.href = newurl;
	});
}

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

                'bLengthChange': true,
		//'bScrollCollapse': true,
//		'iDisplayLength': 10,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,

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

                'bPaginate': false,
                "oLanguage":
                 {
                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                 }
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
		"bPaginate" : false,
        "oLanguage":
         {
            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
         }
	}).show();
}
