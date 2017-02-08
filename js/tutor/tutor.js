var studentsDT = null;

function initDoc(isSuperTutor) {
	$j(document).ready(function() {
		var appointmentsTables = ["sortable_S","sortable_event_proposed"];

		for (var i=0; i < appointmentsTables.length; i++ ) {
			$j('.'+appointmentsTables[i]).dataTable( {
				"bLengthChange": false,
				"bFilter":       false,
				"bInfo":         true,
				"bPaginate":     true,
				"bSort":         true,
				"bAutoWidth":    true,
				"bDeferRender":  true,
                                "bJQueryUI":     true,
                                "aaSorting": [[ 0, "desc" ]],
                "oLanguage": {
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
                },
				"aoColumns": [
				              { "sType": "date-euro" },
				              null,
				              null
				             ]
			}).show();
		}

		$j(".sortable").dataTable( {
			"bLengthChange": true,
			"bFilter":       true,
			"bInfo":         true,
			'bPaginate':     true,
			"bSort":         true,
			"bAutoWidth":    true,
			"bDeferRender":  true,
                        "bJQueryUI":     true,
                        "aaSorting": [[ 0, "desc" ]],
            "oLanguage": {
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



                /***/
		$j(".sortable_A").dataTable( {
			"bLengthChange": true,
			"bFilter":       true,
			"bInfo":         true,
			'bPaginate':     true,
			"bSort":         true,
			"bAutoWidth":    true,
			"bDeferRender":  true,
                        "bJQueryUI":     true,
                        "aaSorting": [[ 4, "desc" ]],
            "oLanguage": {
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
            },
                        "aoColumns": [
                                      { "sType": "date-euro" },
                                      null,
                                      null,
                                      null
                                     ]
		}).show();

		var aoColumnDefs = [];
		var aoColumns = [ null, null, { 'sType': "date-eu" }, null, null ];
		if (!isSuperTutor) {
				var unsortableCols = [];
				var preassignedLastCol = $j("#table_preassigned_students>thead>tr>th").length-1;
				if (preassignedLastCol >= 0) {
					unsortableCols.push(preassignedLastCol);
				}
				aoColumnDefs.push({ "bSortable": false, "aTargets": unsortableCols });
		} else {
			aoColumns.push (null);
		}

		studentsDT = $j("#table_preassigned_students").dataTable( {
			"bLengthChange": true,
			"bFilter":       true,
			"bInfo":         true,
			'bPaginate':     true,
			"bSort":         true,
			"bAutoWidth":    true,
            "bJQueryUI":     true,
            "oLanguage": {
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
            },
            "aaSorting": [[ 0, "asc" ]],
			"aoColumnDefs": aoColumnDefs,
			"aoColumns": aoColumns
		}).show();

		initToolTips();

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

	});
}
var appWindow;
function sendEventProposal (userID) {
	$j.when(getHelpServiceID()).done(function(helpServiceID) {
		if (helpServiceID>0) {
			appWindow.location = HTTP_ROOT_DIR +
			'/comunica/send_event_proposal.php?id_user='+ userID +
			'&id_course='+parseInt(helpServiceID);
		}
	})
	.fail(function() { alert( $j('#noHelpServiceMSG').text() ); } );
}

function getHelpServiceID() {
	var d = $j.Deferred();

	if ($j('#helpServiceID').length<=0 && $j('#selectServiceDialog').length<=0) d.reject();
	else if ($j('#helpServiceID').length>0) {
		appWindow = openMessenger('',800,600);
		d.resolve(parseInt($j('#helpServiceID').val()));
	} else {
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
			d.resolve(-1);
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

/* Formatting function for row details - modify as you need */
function getRequestsDetails (studentID) {
	return $j.ajax({
	    type	: 'GET',
	    url	: HTTP_ROOT_DIR+ '/tutor/ajax/getStudentPendingInstances.php',
	    data	: { studentID: studentID },
	    dataType :'json'
	});
}

function toggleManageRequest(jQueryObj, studentID) {
	if (studentsDT !== null) {
		var tr = $j(jQueryObj).closest('tr')[0];
		var icon = $j(jQueryObj).children('i.icon').first();

		if (studentsDT.fnIsOpen(tr)) {
			icon.toggleClass('down').toggleClass('up');
			studentsDT.fnClose(tr);
		} else {
			$j.when(getRequestsDetails(studentID))
			.done(function (response) {
				if ('html' in response && response.html.trim().length>0) {
					icon.toggleClass('down').toggleClass('up');
					studentsDT.fnOpen( tr, response.html, 'details' );
				}
			});
		}
	}
}

function  initToolTips() {
  $j('.tooltip').tooltip({
   content : function () {
	   return $j(this).prop('title');
   },
   show : {
           effect : "slideDown",
           delay : 300,
           duration : 100
   },
   hide : {
           effect : "slideUp",
           delay : 100,
           duration : 100
   },
   position : {
           my : "center bottom-5",
           at : "center top"
       }
   });
}
