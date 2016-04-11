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
	},{
		"aTargets": [1,5,6,7,8],
		"sClass" : "center"
	},{
		"aTargets": [6],
		"sType"   : "date-eu",
		"sWidth"  : "10%"
	},{
		"aTargets": [5,7,8],
		"sWidth"  : "8%"
	}];

    datatable = $j('table.doDataTable').dataTable({
		"bJQueryUI": true,
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
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
              "bJQueryUI": true,
              'aoColumnDefs': JSONObj.columnDefs,
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

                      $j(this).find("tbody tr td").each(function(){
                    	// substitute zero with dash
                      	if ($j(this).text()=="0") $j(this).text('-');
                      	// if pattoformativo > 0 substitue the number with a checkbox icon
                      	if($j(this).hasClass('pattoformativo') && parseInt($j(this).text())>0) {
                      		$j(this).html('<i class="icon checked checkbox"></i>');
                      	}
                      });
                  },
              "fnFooterCallback":
            	  function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
            	  	var colToSum = [ 2,3,4,5,9,10,13,14,15,16 ];

            	  	var totals= [];
            	  	for (i=0; i<colToSum.length; i++) totals[i]=0;

            	  	aiDisplay.forEach(function (currentRow){
            	  		for (i=0; i<colToSum.length; i++) {
            	  			var value = parseInt(aaData[currentRow][colToSum[i]]);
            	  			if (isNaN(value)) value=0;
            	  			totals[i] += value;
            	  		}
            	  	});

            	  	var nCells = nRow.getElementsByTagName('th');
            	  	var firstCellText = nCells[0].innerHTML.replace(/\d+/g, '');
            	  	nCells[0].innerHTML = aiDisplay.length.toString() + firstCellText;
            	  	for (i=0; i<colToSum.length; i++) {
            	  		/**
            	  		 * WARNING: columns 6 and 7 are hidden, so if col index is
            	  		 * greater than 7, I must subtract 2 to obtain the footer col index
            	  		 */
            	  		cellIndex = (colToSum[i]>7) ? colToSum[i]-2 : colToSum[i];
            	  		nCells[cellIndex].innerHTML = totals[i];
            	  	}
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
