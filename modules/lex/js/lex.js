/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           lex
 * @version		   0.1
 */

// tells if the uploaded file is ok
var fileError = false;
/**
 * the fancytree object
 * either for switcher to edit the tree
 * or for source zoom association with terms view/edit 
 */
var fancyTreeObj = null;
// the datatable object
var dataTableObj = null;
// the selected row ID
var selectedRowID = null;
// array for the (one and only) html select object in the table
var selectArray = null;

// common options for peke uploader
var commonPekeOptions = {
		allowedExtensions : "zip",
		btnText : "Sfoglia Files..",
		field : 'uploaded_file',
		url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php',
		onFileError: function(file,error) { fileError = true; }
};

function initDoc(maxSize, userId, canEdit) {
	// conver canEdit to a boolean
	canEdit = (canEdit > 0);
	
	$j(document).ready(function() {
		// init the tabs
		$j('#lexmenu').tabs({
			beforeActivate: function( event, ui ) {
				var newHref = $j(ui.newTab).find('a').attr('href');
				if (newHref.indexOf('#')!=0) {
					event.preventDefault();
					document.location.href = newHref;
				} else if ($j(ui.newPanel).find('#editTerms').length>0) {
					// if panel with editTerms is made active
					if (fancyTreeObj==null) fancyTreeObj = initEditfancyTreeObj ($j('#editTerms'));
					
				}
			}
		});

		// sets maximum upload file size
		commonPekeOptions.maxSize = maxSize;
		commonPekeOptions.url += '?userId='+userId+'&sessionVar='+UPLOAD_SESSION_VAR;
		
		/**
		 * set javaScript file upload handler
		 * on file upload success for eurovoc file upload
		 */
		$j('#importfile-eurovoc').pekeUpload($j.extend ({
			onFileSuccess : function() {
				 if (!fileError) doImportEurovoc();
				 fileError = false;
			}
		} , commonPekeOptions));
		
		/**
		 * set javaScript file upload handler
		 * on file upload success for jex file upload
		 */
		$j('#importfile-jex').pekeUpload($j.extend ({
			onFileSuccess : function(file) {
				 if (!fileError) fileError = false;
			}
		} , commonPekeOptions));
		
		// make a selectric
		$j('#tipologia').selectric();
		// make a datepicker
		$j('#data_pubblicazione').datepicker({
			showOtherMonths: true
		});

		
		/**
		 * prevent the jex (aka new fonte) form from
		 * being submitted on enter key press, but add
		 * a new typology if enter is pressed on its own input
		 */
		$j("form[name='jex']").bind("keypress", function(e) {
			  var code = e.keyCode || e.which; 
			  if (code  == 13) {
				  e.preventDefault();
				  if ($j(e.target).attr('id')=='nuova_tipologia') {
					  addTipologia();
				  } 
				  return false;
			  }
			});
		
		// function to call on submit
		$j("form[name='jex']").on('submit', function() { doImportJex(); } );
		
		// autocomplete feature for numero_fonte and titolo_fonte fields
		var fieldName = null;
		
		$j('#numero_fonte, #titolo_fonte').autocomplete({
			minLength: 2,
			search: function( event, ui ) {
				// set the field name before asking for source
				fieldName = $j(event.target).attr('id').replace('_fonte','');
			},
			source: function( request, response ) {
				if (fieldName != null) { 
					// term is already in the request,
					// add tableName and fieldName
					request = $j.extend ({
						tableName : 'fonti',
						fieldName : fieldName
					}, request);
					
					$j.getJSON( HTTP_ROOT_DIR+"/modules/lex/ajax/autocomplete.php", request, function( data, status, xhr ) {
						response( data );
						fieldName = null;
						});
				}
			}
		});
		
		// init buttons
		initButtons();
		// init tooltips
		initToolTips();
		
		// init found elements		
		if ($j('#sourcesTable').length>0) dataTableObj = initDataTables($j('#sourcesTable'),canEdit);
		if ($j('#assetsTable').length>0) dataTableObj = initDataTables($j('#assetsTable'),canEdit);
		
		// show main module container
		if ($j('#lexmenu').length>0) $j('#lexmenu').toggle('fade');
		
		// for performance reason, it's better to load the fancyTreeObj as the last element
		if ($j('#selectEurovocTerms').length>0) {
			// init main fancyTreeObj object
			fancyTreeObj = initfancyTreeObj ($j('#selectEurovocTerms'),canEdit,null);
		}
	});
}

/**
 * add a new tipologia
 */
function addTipologia() {
	if ($j('#nuova_tipologia').length > 0) {
		var typology = $j('#nuova_tipologia').val().trim();
		if (typology.length > 0) {
			$j.ajax({
				type	:	'POST',
				url		:	'ajax/addTypology.php',
				data	:	{ typology: typology },
				dataType:	'json'
			})
			.done  (function (JSONObj) {
				if (JSONObj) {
						if (JSONObj.status=='OK') {
							// Append to original select
							$j('#tipologia').append('<option value='+JSONObj.id+'>' + typology + '</option>');
							// set the value to the new element
							$j('#tipologia').val(JSONObj.id);
							// Refresh Selectric
							$j('#tipologia').selectric('refresh');
							// flash the selectric 3 times
							for(var i=0;i<3;i++) {
								$j('#set_tipologia .selectricWrapper').fadeTo(500, 0.2).fadeTo(500, 1.0);
							}
							// clean input field
							$j('#nuova_tipologia').val('');
						}
						// show response message
						showHideDiv ('',JSONObj.msg,JSONObj.status=='OK');
					}
			});
		} else {
			showHideDiv('',$j('#addTypologyEmptyText').html(),false);
		}
	}
}

/**
 * handles the nuova_fonte_btn click by:
 * - hiding the clicked button
 * - resetting the form
 * - resetting the selectric
 * - cleaning the pekecontainer div
 * - hiding the results iframe and showing
 *   the form with an animation
 */
function addFonte() {
	$j('#nuova_fonte_btn').css('display','none');
	$j("form[name='jex']")[0].reset();
	$j('#tipologia').selectric('refresh');
	$j('.pekecontainer').html('');
	$j('#jexResults').slideUp (500, function(){
		$j("form[name='jex']").slideDown(500);
	});
}

/**
 * shows the add new fonte button
 */
function showAddNewButton() {
	$j('#nuova_fonte_btn').css('display','block');
}

/**
 * loads and displays asset details (text and
 * associated terms) with an ajax call
 * 
 * @param assetID asset id to load
 * @param nTr clicked table row to open after load
 * @param imgObj clicked image to display the ajax loader spinner
 */
function openAssetDetails(assetID, nTr, imgObj) {
	
	imgObj.src = HTTP_ROOT_DIR + '/js/include/jquery/ui/images/ui-anim_basic_16x16.gif';
	
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/getAssetDetails.php',
		data	:	{ assetID: assetID },
		dataType:	'json'
	})
	.done  (function (JSONObj) {
		if (JSONObj)
			{
				if (JSONObj.status=='OK') {
					dataTableObj.fnOpen( nTr, JSONObj.html, 'details' );
				}
			}
	})
	.always (function () {
		imgObj.src = "layout/"+ADA_TEMPLATE_FAMILY+"/img/trasp.png";
	});
}

/**
 * sets the passed array in the fancyTreeObj terms tree
 * 
 * @param selectedNodes the array to set when showing the tree
 */

function setFancyTreeObjSelection (selectedNodes) {
	fancyTreeObj.fancytree("getRootNode").visit(function(node){
		// first unselect and unexpand all expanded and selected nodes
		if (node.isExpanded()) node.setExpanded(false, { noAnimation: true });
		if (node.isSelected()) node.setSelected(false);
		
		// if the current node key is in the passed array
		// selected and make visible the current node
        if ($j.inArray(parseInt(node.key), selectedNodes)>-1) {
        	if (!node.isSelected()) node.setSelected(true, { noAnimation: true });
        	if (!node.isExpanded()) node.makeVisible({ noAnimation: true, scrollIntoView:false });
        }
    });
}

/**
 * shows or hides the fancyTreeObj tree, sets the passed array
 * 
 * @param jqueryObj the table object containing the clicked button 
 * @param selectedNodes the array to set when showing the tree
 */
function showfancyTreeObj(selectedNodes) {
	var speed = 350;
	var clearSelection = (selectedNodes==null); 
	// set the selection
    setFancyTreeObjSelection(selectedNodes);
	
	if (!$j('.assetTreeContainer').is(':visible')) {
		// if tree is not visible, show it with an animation
		$j('.assetTableContainer').animate({ width: '69%' }, speed, function() {
			 $j('.assetTreeContainer').show('slide',speed);
		} );
	} else if (clearSelection) {
		// if selected row was clicked, hide the tree with an animation
		$j('.assetTreeContainer').hide('slide',speed, function() {
			$j('.assetTableContainer').animate({ width: '100%' },speed);
		} );
	}
}

/**
 * gets the nodes selected in the tree terms as an array
 * 
 * @returns {Array} selected nodes ids
 */
function getSelectedTreeNodesArray() {
	var s = fancyTreeObj.fancytree('getTree').getSelectedNodes();
	var selectedNodes = new Array();
	// put selected nodes keys in an array
	for (var i=0,j=0; i<s.length; i++) {
		if ($j.inArray(parseInt(s[i].key), selectedNodes)===-1) selectedNodes[j++] = parseInt(s[i].key); 
	}
	if (selectedNodes.length<=0) selectedNodes[0] = null;
	return selectedNodes;
}

/**
 * saves the associations between terms and asset
 * makes an ajax request to updateAssociatedTerms.php
 * 
 */
function saveTree() {
	
	var assetID = parseInt(selectedRowID.replace(/^.*?:/, ''));
	
	if (!isNaN(assetID)) {
		
		// disable the button
		$j('.saveTreeButton').button('disable');
		
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/updateAssociatedTerms.php',
			data	:	{ selectedNodes: getSelectedTreeNodesArray(), assetID: assetID },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
				showHideDiv (JSONObj.status, JSONObj.msg, JSONObj.status=='OK');
				// redraw the table to reflect changes
				dataTableObj.fnDraw();
			}
		})
		.always (function () { $j('.saveTreeButton').button('enable'); } );
	}
}

/**
 * sets the selected row, or unset if the selected row was clicked
 * shows or hides the fancyTreeObj as needed
 * 
 * @param clickedObj the clicked table row
 * @param selectedNodes the array to set when showing the tree
 * 
 * @returns {Boolean} true if selected row was clicked
 */

function setSelectedRow(clickedObj, selectedNodes) {
	var jQueryObj = $j(clickedObj);
	// if the selected row was clicked, clear the selection
	var clearSelection = (jQueryObj.parents('tr').attr('id')==selectedRowID); 
	
	// deselect all rows
	selectedRowID = null;
    jQueryObj.parents('table tbody').find('tr').each( function() { $j(this).removeClass('selectedRow'); });
    
	// select clicked one
    if (!clearSelection) {
    	selectedRowID = jQueryObj.parents('tr').attr('id'); 
    	jQueryObj.parents('tr').addClass('selectedRow');
    	showfancyTreeObj (selectedNodes);
    } else {
    	showfancyTreeObj (null);
    }
    return clearSelection;
}

/**
 * handles the click on expandAssetButton by:
 * - setting selectedRow (this shows the fancyTreeObj as well)
 * - closing all table rows
 * - opening asset details and its table row
 * 
 * @param clickedObj the clicked table row
 * @param selectedNodes the array to set when showing the tree
 */

function assetExpand(clickedObj, selectedNodes) {
	// set selected row, shows or hides, and set the fancyTreeObj
	var clearSelection = setSelectedRow(clickedObj, selectedNodes);
	
	// close all rows	
	$j(clickedObj).parents('table tbody').find('tr').each( function() {
	    if ( dataTableObj.fnIsOpen(this) ) dataTableObj.fnClose( this );
	});	

	if (!clearSelection) {
		// open the clicked row, if it was not the previously selected one
		var nTr = $j(clickedObj).parents('tr')[0];
		// get the asset if from the row id
    	assetID = parseInt($j(nTr).attr('id').replace(/^.*?:/, ''));
    	if (!isNaN(assetID)) {
    		// clear the tree filter
    		$j("button#resetTreeFilter").click();
    		// Open this row with an ajax loader
    		openAssetDetails(assetID, nTr, clickedObj);    		
    	}    	
	}
}

/**
 * inits jquery buttons
 */
function initButtons() {
	if ($j('#nuova_tipologia_btn').length>0) $j('#nuova_tipologia_btn').button();
	if ($j('#nuova_fonte_btn').length>0) $j('#nuova_fonte_btn').button();
	
	if ($j('.saveTreeButton').length>0) {
		$j('.saveTreeButton').button({
			icons : {
				primary : 'ui-icon-disk'
			},
			text : true
		});
	}
	/**
	 * actions button
	 */
	if ($j('.deleteButton').length>0) {
		$j('.deleteButton').button({
			icons : {
				primary : 'ui-icon-trash'
			},
			text : false
		});
	}
	
	if ($j('.zoomButton').length>0) {
		$j('.zoomButton').button({
			icons : {
				primary : 'ui-icon-zoomin'
			},
			text : false
		});		
	}
	
	if ($j('.linkAssetButton').length>0) {
		$j('.linkAssetButton').button({
			icons : {
				primary : 'ui-icon-link'
			},
			text : false
		});		
	}
}

/**
 * inits the passed element as a dataTable
 * with edit controls, if requested
 * 
 * @param element the element to be dataTabled
 * @param canEdit true if edit controls are required
 * @returns dataTable  object
 */
function initDataTables (element, canEdit) {
	
	var theID = $j(element).attr('id').toString();
	// parameters to init the dataTable
	var params = null;
	// object to be returned
	var oTable = null;
	
	// set parameters according to object to be dataTabled
	if (theID == 'sourcesTable') {
		/**
		 * loads the array to be displayed as html <select>
		 * when editing a table field that has the hasSelect class
		 */
		if (selectArray==null) {
			$j.when(getSelectValues('tipologie'))
			.done  (function (JSONObj) {		
				if (JSONObj) {
						if (JSONObj.status=='OK') {
							selectArray = JSONObj.data;
						}
				}
			});
		}
		
		params = {	
			"aaSorting": [[ 2, "asc" ]],
			aoColumns : [
		                      // first empty column generated by ADA HTML engine, let's hide it
		                      { "sName":"module_lex_fonti_id", "bSearchable": false, "bVisible": false },
		                      // hide number field
		                      { "sName":"numero" ,"sWidth" : "9%" , "bVisible": false } ,
		                      { "sName":"titolo" },
		                      { "sName":"data_pubblicazione", "sType": "date-eu", "sWidth" : "15%" },		                      
		                      { "sName":"tipologia", "sClass":"hasSelect", "sWidth" : "20%" },
		                      { "sName":"azioni", "sClass": "center noteditable", "bSearchable" : false, "bSortable" : false, "sWidth" : "8%" }
		                     ],
			sAjaxSource : 'ajax/getSourcesTableData.php'
		};
	} else if (theID == 'assetsTable') {
		// get the source id
		var sourceID = parseInt($j('[id^=assetsContainer_]').attr('id').replace(/^.*?_/, ''));
		
		/**
		 * loads the array to be displayed as html <select>
		 * when editing a table field that has the hasSelect class
		 */
		if (selectArray==null) {
			$j.when(getSelectValues('stati'))
			.done  (function (JSONObj) {		
				if (JSONObj) {
						if (JSONObj.status=='OK') {
							selectArray = JSONObj.data;
						}
				}
			});
		}
		
		if (!isNaN(sourceID)) {
			params = {
				"aaSorting": [[ 2, "asc" ]],
				aoColumns : [
			                      // first empty column generated by ADA HTML engine, let's hide it
			                      { "sName":"module_lex_assets_id", "bSearchable": false, "bVisible": false },
			                      { "sName":"expandAssetButton", "sClass": "center noteditable expandAsset", "bSortable": false, "bSearchable" : false, "sWidth" : "2%" },			                      
			                      { "sName":"label", "sWidth" : "35%" } ,
			                      { "sName":"url", "sWidth" : "32%", "bSearchable": false, "bVisible": false  } ,
			                      { "sName":"data_inserimento", "sClass": "center", "sType": "date-eu", "sWidth" : "7%" },
			                      { "sName":"data_verifica", "sType": "date-eu", "sClass": "center noteditable", "sWidth" : "7%" },
			                      { "sName":"stato", "sClass":"hasSelect", "sWidth" : "4%" },
			                      { "sName":"azioni", "sClass": "center noteditable", "bSearchable" : false, "bSortable" : false, "sWidth" : "2%" }
			                     ],
				sAjaxSource : 'ajax/getAssetsTableData.php',
				// send the sourceID to the server
				"fnServerParams": function ( aoData ) {
				      aoData.push( { "name": "sourceID", "value": sourceID });
				},
			};
		}
	}
	
	if (params!=null) {
		/**
		 * build a datatable by extending common default params
		 * with specific parms array set above 
		 */
		oTable = element.dataTable(
    		$j.extend({} , params,
    				{
				 		"bJQueryUI": true,
				        "bFilter": true,
				        "bInfo": true,
				        "bSort": true,
				        "bPaginate" : true,
				        "bProcessing": true,
				        "bServerSide": true,
						"oLanguage": {
				            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
				        },
						"fnDrawCallback": 
							function () {
								initButtons();
								initToolTips();
								
								// set css class for selected row in the assetsTable
								if ($j(dataTableObj).attr('id')=='assetsTable') {
									$j('#'+dataTableObj.attr('id')+' tbody tr').each( function() {
										
										if ((typeof $j(this).attr('id') != 'undefined')
												&& $j(this).attr('id')==selectedRowID) {
											$j(this).addClass('selectedRow');
											// prepare parameters for openAssetDetails
											assetID = parseInt($j(this).attr('id').replace(/^.*?:/, ''));
											imgObj = $j(this).find('td img.expandAssetButton')[0];
											openAssetDetails(assetID, this, imgObj);
										}
									});
								}
								
								// put the sort icon outside of the DataTables_sort_wrapper div
								// for better display styling with CSS
								oTable.find("thead th div.DataTables_sort_wrapper").each(function(){
									sortIcon = $j(this).find('span').clone();
									$j(this).find('span').remove();
									$j(this).parents('th').append(sortIcon);
								});
								
								// if user cannot edit, we're done
								if (!canEdit) return;
								
								/**
								 * attach editable for cells that must have a select type
								 */
								$j('#'+element.attr('id')+' tbody td.hasSelect').not('.noteditable').editable( 'ajax/updateRow.php', {
									 data    : function() {
										 // selected value is autodetected											
										 return selectArray;
									 },
								     type    : 'select',
								     submit  : 'OK',
								     "placeholder" : "",
								     "submitdata"  : function() {
								    	 return dataTableSubmitData( this, oTable );
									 },
									 "callback": function( value, settings ) {
										 return dataTableSubmitCallback( this, oTable, value, true );
									 }
								});
								
								/**
								 * attach an editable for cells that do not have 'hasSelect' class
								 * and therefore have a standard input field to be edited
								 */
								$j('#'+element.attr('id')+' tbody td').not('.noteditable,.hasSelect').editable( 'ajax/updateRow.php', {
									"placeholder" : "",
									"submitdata"  : function() {
										return dataTableSubmitData( this, oTable );
									},
									"callback": function( value, settings ) {
										return dataTableSubmitCallback( this, oTable, value, false );
									},
									"height": "14px"
								});
							}
    				})
		).show();		
	}
	return oTable;
}

/**
 * function called after editable has done its submission
 * 
 * shows the confirmation popup to the user and updates
 * the edited row with returned value.
 * 
 * NOTE: returned value can be an array. If this is the case
 * then the array is looped and if a column exists having the
 * same sName of the array keys, the value is updated accordingly.
 * 
 * @param cellObj the cell where the editable is
 * @param oTable  the datatable object where the cell is
 * @param value   the value returned from server
 * @param isSelect true if editable is an html select
 */
function dataTableSubmitCallback( cellObj, oTable, value, isSelect )  {
	$j(cellObj).text('');
	try {
		JSONObj = JSON.parse (value);
		showHideDiv(JSONObj.status, JSONObj.msg, JSONObj.status=='OK');
		// get editable control position
		var position = oTable.fnGetPosition( cellObj );
		// update editable cell value without redraw
		if (typeof JSONObj.value !='object') {
			if (isSelect) {
				oTable.fnUpdate (selectArray[JSONObj.value], position[0], position[2], false);
			} else {
				oTable.fnUpdate (JSONObj.value, position[0], position[2], false);
			}
		} else {
			/**
			 * if return object value field is an object itself than
			 * we must update the cell were the editable was (i.e. the current cell)
			 * using the JSONObj.value.value property and then loop all other properties
			 * and update fields accordingly. e.g. JSONObj.value.data_verifica will
			 * hold the new value for the corresponding column 
			 */
			
			// first update the current cell
			if (isSelect) {
				oTable.fnUpdate (selectArray[JSONObj.value.value], position[0], position[2], false);				
			} else {
				oTable.fnUpdate (JSONObj.value.value, position[0], position[2], false);
			}
			
			// delete its value in the returned object
			delete JSONObj.value.value;
			// loop the other object properties, if any and update columns accordingly
			for (var lookfor in JSONObj.value) {
				/**
				 * after the loop,
				 * if lookfor is found in columns sName: colFound is true and colPos is the col index
				 * else colFound is false
				 */
				for (var colPos=0, colFound=false; colPos<oTable.fnSettings().aoColumns.length && !colFound; colPos++) {
					if (colFound = oTable.fnSettings().aoColumns[colPos].sName==lookfor) --colPos;
				}
				
				if (colFound) {
					// if col is found, update it
					oTable.fnUpdate (JSONObj.value[lookfor], position[0], colPos, false);
				}				
			}
		}
	} catch (err) {
		showHideDiv('', 'Error: '+err, false);
		/* Redraw the table from the new data on the server */
		oTable.fnDraw();
	}		
}

/**
 * function called just before the editable is being submitted
 * to retrieve and pass proper parameters along with the ajax call
 * 
 * @param cellObj the cell where the editable is
 * @param oTable  the datatable object where the cell is
 * @returns object with data for the updateRow.php call
 */
function dataTableSubmitData( cellObj, oTable ) {
	// get editable control position
	var position = oTable.fnGetPosition( cellObj ); 
	var row = position[0];
	var col = position[2];
	// get the row
	var dataRow = oTable.fnGetData(row);
	// row id is <table_name>:<id_value>
	// let's split to get both
	var rowDetails = dataRow['DT_RowId'].split(':');
	// get the column
	var oSettings = oTable.fnSettings();
	// the sName holds the table field to update
	var colName = oSettings.aoColumns[col].sName;
	// send data with ajax call
	return { columnName : colName, table : rowDetails[0],
		     id : rowDetails[1], oldValue: dataRow[col] };
}

/**
 * gets the html select object values and labels
 * this is done with a synchronous call because
 * the datatable must have the values to populate
 * the select as soon as it's  being drawn 
 * 
 * @param what the select to get
 * @returns a jQuery promise resolved when ajax call return
 */
function getSelectValues(what) {
	return $j.ajax({
		type	:	'GET',
		url		:	'ajax/getSelectValuesTableData.php',
		data	:	{ what: what },
		dataType:	'json',
		async   :   false
	});	
}

/**
 * inits the tooltips
 */
function initToolTips() {
	// inizializzo i tooltip sul title di ogni elemento!
	if ($j('.tooltip').length>0) {
		$j('.tooltip').tooltip(
				{
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
}

/**
 * inits the fancyTree object in edit tree mode * 
 * 
 * @param element the jQuery object to attach to
 * 
 * @returns fancyTree
 */
function initEditfancyTreeObj (element) {
	// set the options for this kind of tree
	var savePromise = null;
	var options = {
			checkbox:false,
			extensions: [ "childcounter", "filter", "edit" ],
			renderNode: function(event, data) {
				if (!data.node.isFolder() && data.node.data.isUserDefined)
					 $j(data.node.span).find('> span.fancytree-icon')
					 	.removeClass('fancytree-icon').addClass('fancytree-custom-icon ui-icon ui-icon-pencil');
			},
			edit: {
					triggerStart: ["f2", "dblclick", "shift+click", "mac+enter"],
					triggerCancel: ["esc", "tab", "click"],
					// Return false to prevent cancel/save (data.input is available)
				    beforeClose: function (event, data) {
				    	if(data.save && data.input.val().trim().length<=0) {
				    		showHideDiv('',$j('#nonEmptyMsg').html(),false);
				    		return false;
				    	} else if (!data.save) {
				    		if (data.node.data.isNew) data.node.remove();
				    	}
				    },
				    beforeEdit: function(event, data) {
				    	// edit enabled only for userdefined nodes
				    	return data.node.data.isUserDefined;				    	
				    },
				    edit: function (event,data) {
				    	data.input.select();
				    },
				    // Save data.input.val() or return false to keep editor open 
				    save: function (event, data) {
				    	// get the new value
				    	var value = data.input.val().trim();
				    	// save only user defined nodes
				    	if (data.node.data.isUserDefined) {
				    		// prepare common data to be POSTed
				    		var POSTdata = {  
									domaineRootNodeID : data.node.getParentList()[0].key,
									parentNodeID : data.node.parent.key,
									term : value
								};
				    		
				    		// if it's not a new node, add its key to the POST
					    	if (!data.node.data.isNew) {
					    		// add selected node key to update node
					    		POSTdata = $j.extend ({
					    			descripteur_id : data.node.key
					    		},POSTdata);					    		
					    	}
				    		
				    		// if it's not been edited or the input field is empty
				    		// delete it if it's new
				    		if (data.node.title == value || value.length<=0) {
				    			if (data.node.data.isNew) data.node.remove();
				    		} else {
						    	// promise is handled in close callback
						    	savePromise = $j.ajax({
									type	:	'POST',
									url		:	'ajax/saveTerm.php',
									data	:	POSTdata,
									dataType:	'json'
								});
				    		}
				    	}
				    },
				    close : function (event, data) {
				    	// handle promise made on save callback
				    	if (savePromise!=null)
				    	{
					    	$j.when(savePromise)
					    	.done(function (JSONObj) {
								if (JSONObj) {
									if (JSONObj.status=='OK') {										
								    	data.node.data.isNew = false;
								    	data.node.key = JSONObj.nodeKey;
								    	// sort the active branch after closing the input
								    	if(data.node.parent!=null) data.node.parent.sortChildren();
									} else if (JSONObj.status=='ERROR') {
										if (data.node.data.isNew) data.node.remove();
									}
									showHideDiv('',JSONObj.msg,JSONObj.status=='OK');
								} else {
									showHideDiv('',$j('#nodeSavingFailMsg').html(),false);
								}
					    	})
					    	.always (function() { savePromise=null; }) 
					    	.fail(function () {
					    		if (data.node.data.isNew) data.node.remove();
					    		else {
					    			fancyTreeObj.fancytree('getTree').reload();
					    		}
					    		showHideDiv('',$j('#nodeSavingFailMsg').html(),false);
					    	});				    		
				    	}
				    }
			}
	};
	
	// init the tree with its own options to handle
	// inline edit extension and event handling
	var returnTree = initfancyTreeObj(element, false, options );
	
	// attach a context menu
    returnTree.contextmenu({
        delegate: "span.fancytree-title",
        menu: "#treeContextMenu",
        show: { effect: "fade", duration: "fast" },
		hide: { effect: "fade", duration: "fast" },
        beforeOpen: function(event, ui) {
          var node = $j.ui.fancytree.getNode(ui.target);
          /**
           * show menu only if node has children
           * and is not in the editing state
           */
          if (node.hasChildren() || node.isEditing()) return false;
          else {
        	  returnTree.contextmenu("enableEntry", "edit", node.data.isUserDefined);
        	  returnTree.contextmenu("enableEntry", "delete", node.data.isUserDefined);
          }
        },
        select: function(event, ui) {
          var node = $j.ui.fancytree.getNode(ui.target);
          switch (ui.cmd) {
          case 'new':
              // delay the event, so the menu can close and the click event does
              // not interfere with the edit control
        	  setTimeout (function() { addNewTreeNode(node); } ,100);
        	  break;
          case 'edit':
        	  setTimeout (function() { node.editStart(); } ,100);
        	  break;
          case 'delete':
        	  deleteSelectedTreeNode(node);
        	  break;
          default :
        		  showHideDiv ('Menu Click','Action not defined!',false);
        	  break;
          }
        }
      });
    
    initfancyTreeFilter();
    
	return returnTree;
}

/**
 * adds a new node as a sibling to the selected node
 * this does only affect the html structure, no call
 * to the DB is made at all!
 * 
 * Saving is handled in edit property/save callback of the fanyTreeObj
 * 
 * @param node the node to add to
 */
function addNewTreeNode(node) {
    refNode = node.appendSibling({
    	title: $j('#defaultNewNodeTitle').text(),
        isNew: true,
        isUserDefined: true
    });    
    refNode.editStart();
}

/**
 * removes the selected node from the tree
 * 
 * @param node
 */
function deleteSelectedTreeNode(node) {
	/**
	 * TODO: ajax remove here!!!
	 */
	alert (node.key);
	// node.remove();
}

/**
 * inits the fancyTree object
 * 
 * @param element the jQuery object to attach to
 * @param canEdit true if user can edit associations, sets checkboxes to active
 * @param extraOptions object of extra options to be used on tree init
 * 
 * @returns fancyTree
 */
function initfancyTreeObj (element, canEdit, extraOptions) {
	
	var options = {
		extensions: ["childcounter", "filter"],
	    childcounter: { deep: false, hideZeros: true, hideExpanded: true },
	    filter: { mode: "hide" },
	    debugLevel: 0,
	    checkbox: true,
	    selectMode: 2,
	    select: function(event, data) {
	    	
	    	if (!canEdit) return;
	    	
	        var isSelected = data.node.isSelected();
	  	  	var nodeKey = data.node.key;
	  	  	
	  	  	root = $j(this).fancytree("getTree").rootNode;
	    
	  	  	var targetNodes = root.findAll(function(node){
	  	  		return node.key==nodeKey;
	  	  	});
	  	  	
	  	  	if (targetNodes.length>1) {
	  	  		for (var i=0; i<targetNodes.length; i++) {
	  	  			targetNodes[i].setSelected(isSelected);
	  	  		}
	  	  	}       
	      },
	      beforeSelect: function(event, data){
	          /**
	           *  handle all code generated events and discard
	           *  mouse or keyboard generated events if the user cannot edit
	           */ 
	    	  return (event.which) ? canEdit : true;        	  
	      },
	      source: {
	        url: 'ajax/getEurovocTree.php'
	      },
	};
	
	var returnTree = element.fancytree($j.extend(options,extraOptions));
	initfancyTreeFilter();
	return returnTree;
}

/**
 * inits the text input to filter the 
 * fancyTree and the button to clear it
 */
function initfancyTreeFilter() {
	/**
	 * init tree filter text input
	 */
	if ($j("input#treeFilterInput").length>0) {
	    $j("input#treeFilterInput").keyup(function(e){
	        var match = $j(this).val();
	        
	        if(e && e.which === $j.ui.keyCode.ESCAPE || $j.trim(match) === ""){
	          $j("button#resetTreeFilter").click();
	          return;
	        } else if (e.which === $j.ui.keyCode.ENTER) {
		        // Pass a string to perform case insensitive matching
		        // second parameter is to filter leaves only
		        n = fancyTreeObj.fancytree('getTree').filterNodes(match, false);
		        
		        // if any matched nodes, expand them
		        if (n>0) {
		        	fancyTreeObj.fancytree("getRootNode").visit(function(node){
		        		if (node.title.toLowerCase().indexOf(match.toLowerCase())>-1) {
		                	if (!node.isExpanded()) node.makeVisible({ noAnimation: true, scrollIntoView:false });
		        		}
		            });
		        	// fix hidden and shown elements
		        	$j('span.fancytree-hide').parents('li').css('display','none');
		        	$j('span.fancytree-match').parents('li').css('display','block');
		        }			        
		        $j("button#resetTreeFilter").attr("disabled", false);
	        }
	      }).focus();
	}

    /**
     * init clear button
     */
	if ($j("button#resetTreeFilter").length>0) {
	    $j("button#resetTreeFilter").click(function(e){
	    	// clear text field and tree filter
	        $j("input#treeFilterInput").val("");
	        fancyTreeObj.fancytree('getTree').clearFilter();
	    	// fix hidden and shown elements
	        $j('span.fancytree-node').parents('li').css('display','block');
	        // reset object as it previously was
	        setFancyTreeObjSelection(getSelectedTreeNodesArray());
	      }).attr("disabled", true);
	}    
}

/**
 * function to delete a source with an ajax call
 * 
 * TODO: not yet implemented
 * 
 * @param jqueryObj
 * @param id_source
 * @param message
 */
function deleteSource (jqueryObj, id_source, message) {
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{
		showHideDiv('','Funzione ancora non implememtata',false);
		return;
		
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_source.php',
			data	:	{ id: id_source },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj)
				{
					if (JSONObj.status=='OK')
					{
						// deletes the corresponding row from the DOM with a fadeout effect
						jqueryObj.parents("tr").fadeOut("slow", function () {
							var pos = datatable.fnGetPosition(this);
							datatable.fnDeleteRow(pos);
							});
					}
				}
		});
	}
}

/**
 * Runs the import from JEX by:
 * - setting the submit target to the results iframe
 * - showing the iframe and hiding the form with an animation
 * - submitting the form by returning true
 */
function doImportJex() {
	
	var theForm = $j("form[name='jex']");	
	theForm.attr("target","jexResults");
	
	theForm.slideUp(500, function() {
		$j("#jexResults").slideDown(500, function (){
			return true;
		});
	});	
}

/**
 * Runs the import from EuroVoc XML by:
 * - setting the submit target to the results iframe
 * - showing the iframe and hiding the form with an animation
 * - forcing the form to being submitted, since no button is clicked
 */
function doImportEurovoc() {
	
	var theForm = $j("form[name='eurovoc']");	
	theForm.attr("target","eurovocResults");

	theForm.slideUp(500, function() {
		$j("#eurovocResults").slideDown(500, function (){
			theForm.submit();			
		});
	});
}
