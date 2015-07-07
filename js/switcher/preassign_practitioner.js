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
	oTable = $j('#table_preassignment').dataTable({
		"bJQueryUI": true,
		"bFilter": true,
		"bInfo": true,
		"bSort": true,
		"bAutoWidth": true,
		"aaSorting": [[ 1, "asc" ]],
		"iDisplayLength": 50,
        "aoColumns": [
                      { "bSearchable": false, "bSortable": false, "sClass":"centerAlign", "sWidth": "4%"},
                      { "sWidth": "4%", "sClass":"centerAlign" },
                      { "sWidth": "28%"},
                      { "sWidth": "28%"},
                      { "sWidth": "28%"}
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
       
       show :     {
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