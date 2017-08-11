/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var datatable;

function initDoc(){
	
	var colDefs = [{
		"aTargets": [0],
		"bSortable":false,
		"sClass" : "actionCol",
		"sWidth" : "1%"
	}];
    
    datatable = $j('table.doDataTable').dataTable({
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
	});
}

var openedRow = null;
function toggleTutorDetails(tutor_id,imgObj) {
  var closeOpenedRowOnClick = true;
  
  var nTr = $j(imgObj).parents('tr')[0];
  var oTable = $j(nTr).parents('table').dataTable();
  
  if (closeOpenedRowOnClick && openedRow!=null && oTable.fnIsOpen(openedRow)) {
		$j(openedRow).find('td.actionCol > img').attr('src',HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_open.png");
		oTable.fnClose(openedRow);
  } 
  
  if (!closeOpenedRowOnClick || openedRow != nTr) {
	  openedRow = nTr;
      /* Open this row */
      imgObj.src = HTTP_ROOT_DIR+"/js/include/jquery/ui/images/ui-anim_basic_16x16.gif";
      var imageReference=imgObj;
      $j.when(getTutorDetails(tutor_id))
      .done   (function( JSONObj ) { 
          oTable.fnOpen( nTr, JSONObj.html, 'details' );
          if(JSONObj.status==='OK'){
              $j('.tutor_table').not('.dataTable').dataTable({
              'aoColumnDefs': JSONObj.columnDefs,
              "oLanguage": {
                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
              } 
              });
          }
     })
     .fail   (function() { 
          console.log("ajax call has failed"); 
	} )
      .always(function (){
          imageReference.src = HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_close.png";
      });
  } else openedRow = null;
}

function getTutorDetails ( idTutor ) {
    return $j.ajax({
       type	: 'GET',
       url	: 'ajax/get_tutorDetails.php',
       data	: {'id_tutor': idTutor},
       dataType :'json'
       });
}
