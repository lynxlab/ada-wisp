/**
 * Preassign users - this module lets the swithcer preassign a tutor to students
 *
 *
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

var oTable = null;

function initDoc(op){
	/**
	 * to sort by 'voto maturità' (67/100, 38/60, 98/100....)
	 * evaluate the division of the passed grade (0.67, 0.6333, 0.98...)
	 */
	$j.extend( $j.fn.dataTableExt.oSort, {
	    "mathexpr-num-pre": function (a) {
	    	var expr = />{1}([0-9]{1,3}\/{1}[0-9]{1,3})<{1}/g;
	    	var n = expr.exec(a);
	    	if (n==null || n.length<2) return 0;
	    	else return parseFloat(eval(n[1]));
	    },
	    "mathexpr-num-asc": function (a, b) {
	        return a - b;
	    },
	    "mathexpr-num-desc": function (a, b) {
	        return b - a;
	    }
	});
	
	createDataTable();
	initToolTips();
	
	$j('button[name=selectAll]').click(function() {
		$j(this).blur();
		$j('input:checkbox').each(function(i,el) {
			$j(this).prop('checked',!$j(this).prop('checked'));
		})
	});
	
	if (op=='edit') {
		$j('#selTutor').change(function() {
			reloadWithPractitioner($j(this).val());
		});
	}
}

function reloadWithPractitioner(id) {
	var pos = document.location.href.indexOf('?');
	var newurl = (pos>-1) ? document.location.href.substr(0,pos) : document.location.href;
	document.location.href = newurl+'?practitioner_id='+id;
}

function createDataTable() {
	if ($j("#table_preassignment").length<=0) return;
	
	// fix table footer span before initializing the datatable
	var numCols = $j("#table_preassignment").find('tr')[0].cells.length;
	$j('#table_preassignment tfoot tr th').attr('colspan', numCols.toString());
	
	oTable = $j('#table_preassignment').dataTable({
		"bJQueryUI": true,
		"bFilter": true,
		"bInfo": true,
		"bSort": true,
		"aaSorting": [[ 2, "asc" ]],
		"iDisplayLength": 50,
		'aoColumnDefs': [{ "sWidth" : "4%",
						   "sClass" : "centerAlign",
						   "aTargets" : [0,1] },
						 { "bSortable": false, 
						   "bSearchable":false,
						   "sWidth" : "1%",
						   "aTargets": [0] },
						 { "bSortable": false, "sType" : "html", "aTargets":[6] },
						 { "sWidth": "9%", "aTargets":[7] },
						 { "sType" : "mathexpr-num", "aTargets":[9] }
						],
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
			}
	});
}

function checkPreassignForm(selectATutorMSG, selectAStudentMSG) {
	var retval = false;	
	if ($j('select[name=selTutor]').val()==0) {
		alert (selectATutorMSG);
	} else if (!$j('input:checkbox').is(':checked')) {
		alert (selectAStudentMSG);
	} else retval = true;
	return retval;
}

function checkEditPreassignForm(selectATutorMSG, selectAStudentMSG, question) {
	var retval = false;
	if (checkPreassignForm(selectATutorMSG, selectAStudentMSG)) retval = confirm(question);
	return retval;	
}

function goToEdit(selectATutorMSG) {
	if ($j('select[name=selTutor]').val()==0) {
		alert (selectATutorMSG);
	} else reloadWithPractitioner($j('select[name=selTutor]').val());
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