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
var lastSubmit = -1;
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
// options passed to the initDoc function
var opts = null;

// common options for peke uploader
var commonPekeOptions = {
		allowedExtensions : "zip",
		btnText : "Sfoglia Files..",
		field : 'uploaded_file',
		url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php',
		onFileError: function(file,error) { fileError = true; }
};

function initDoc(maxSize, userId, canEdit, passedOpts) {
	// conver canEdit to a boolean
	canEdit = (canEdit > 0);

	// default options to an empty object
	opts = passedOpts || {};

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
					if (fancyTreeObj==null) fancyTreeObj = initEditFancyTreeObj ($j('#editTerms'));
				} else if ($j(ui.newPanel).find('#sourcesTable').length>0) {
					// if panel with sources is made active, reload table to reflect possible changes
					if (dataTableObj!=null) dataTableObj.fnDraw();
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
		$j('#tipologia, #categoria, #classe').selectric();
		// hook onchange event of select elements
		$j('#tipologia').on ('change',function(){
			$j.when(updateSelect('categoria')).done( function() { updateSelect('classe'); } );
		});
		$j('#categoria').on ('change',function(){
			updateSelect('classe');
		});
		
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
		$j("form[name='jex']").on('submit', function(e) { 
			/**
			 * event timestamp is not valorized correctlry in firefox
			 * due to a bug opened since 2004, see:
			 * 
			 * http://api.jquery.com/event.timeStamp/
			 */
			e.timeStamp = (new Date).getTime();

			if (lastSubmit+500 >  e.timeStamp) {
				return;
			} else {
				lastSubmit = e.timeStamp;
			}
			
			doImportJex(); } );

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
			fancyTreeObj = initFancyTreeObj ($j('#selectEurovocTerms'),canEdit,null);
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
	$j('#jexResults').contents().find('html').html('');
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
function showFancyTreeObj(selectedNodes) {
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
		showFancyTreeObj (selectedNodes);
	} else {
		showFancyTreeObj (null);
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
	
	if ($j('.linkSourceButton').length>0) {
		$j('.linkSourceButton').button({
			icons : {
				primary : 'ui-icon-link'
			},
			text : false
		});
	}
	
	if ($j('.downloadButton').length>0) {
		$j('.downloadButton').button({
			icons : {
				primary : 'ui-icon-disk'
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
			             	  { "sName":"numero", "bVisible": false } ,
			             	  { "sName":"titolo" },
			             	  { "sName":"data_pubblicazione", "sType": "date-eu", "sWidth" : "15%" },
			             	  { "sName":"tipologia", "sClass":"hasSelect", "sWidth" : "15%" },
			             	  { "sName":"categoria", "sClass":"hasSelect editCategoria", "sWidth" : "15%" },
			             	  { "sName":"classe", "sClass":"hasSelect editClasse", "sWidth" : "15%" },
			             	  { "sName":"azioni", "sClass": "center noteditable", "bSearchable" : false, "bSortable" : false, "sWidth" : "7%" }
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
				             	  { "sName":"label", "sClass":"assetLabel", "sWidth" : "31%" } ,
				             	  { "sName":"url", "bSearchable": false, "bVisible": false  } ,
				             	  { "sName":"data_inserimento", "sClass": "center", "sType": "date-eu", "sWidth" : "7%" },
				             	  { "sName":"data_verifica", "sType": "date-eu", "sClass": "center noteditable", "sWidth" : "7%" },
				             	  { "sName":"stato", "sClass":"hasSelect", "sWidth" : "4%" },
				             	  { "sName":"abrogato", "sClass": "center noteditable abrogated", "sWidth" : "4%" },
				             	  { "sName":"azioni", "sClass": "center noteditable", "bSearchable" : false, "bSortable" : false, "sWidth" : "2%" }
				            ],
				sAjaxSource : 'ajax/getAssetsTableData.php',
				// send the sourceID to the server
				"fnServerParams": function ( aoData ) {
				      aoData.push( { "name": "sourceID", "value": sourceID });
				      if (opts.assetID) {
				    	  aoData.push( { "name": "assetID", "value": opts.assetID });  
				      }
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
						"oSearch" : { 
							"sSearch" : ('undefined' != typeof opts.filter && opts.filter.length>0) ? opts.filter : ''
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
									
									// remove aborgated link if user cannot edit
									$j('#'+dataTableObj.attr('id')+' tbody td.abrogated').each(function() {
										if (!canEdit) $j(this).html($j(this).text());
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
								 * attach editable for cells that must have a select type AND ARE categoria and classe
								 */
								$j('#'+element.attr('id')+' tbody td.hasSelect.editCategoria, '+
								   '#'+element.attr('id')+' tbody td.hasSelect.editClasse').not('.noteditable').editable( 'ajax/updateRow.php', {
									   loadurl : 'ajax/getSelectOptions.php',
									   loaddata :
											 function() {
												 // get editable control position										
												 var position = oTable.fnGetPosition( this );
												 var row = position[0];
												 var col = position[2];
												 // get the row
												 var dataRow = oTable.fnGetData(row);											
												 // get the column
												 var oSettings = oTable.fnSettings();
												 // the sName holds the table field to update
												 var colName = oSettings.aoColumns[col].sName;
													
												 var data = {
														 what: colName,
														 typology : dataRow[4], // 4 is tipologia!
														 returnArray: true														 
												 };
												 
												 if (colName=='categoria') {
													 return	data;													 
												 } else if (colName=='classe') {
													 return	($j.extend(data,{
														 category : (dataRow[5]!=null) ? dataRow[5] : 'null' // 5 is categoria!
													 }))
												 }
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
								 * attach editable for cells that must have a select type AND ARE NOT categoria and classe
								 */
								$j('#'+element.attr('id')+' tbody td.hasSelect').not('.noteditable, .editCategoria, .editClasse').editable( 'ajax/updateRow.php', {
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
		if (JSONObj.length<=0) {
			/* Redraw the table from the new data on the server */
			oTable.fnDraw();
			return;
		}
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
			if (isSelect && selectArray[JSONObj.value.value]) {
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
	var data = { columnName : colName, table : rowDetails[0],
			     id : rowDetails[1], oldValue: dataRow[col] };
	if (colName == 'categoria' || colName == 'classe') {
		data = $j.extend(data,{
			typology : dataRow[4]
		});
		if (colName=='classe' && dataRow[5]!=null) {
			data = $j.extend(data,{
				category : dataRow[5] // 5 is categoria!
			});
		}
	}
	// send data with ajax call
	return data;
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
function initEditFancyTreeObj (element) {
	// set the options for this kind of tree
	var savePromise = null;
	var options = {
			checkbox:false,
			extensions: [ "childcounter", "filter", "edit" ],
			renderNode: function(event, data) {
				if (!data.node.isFolder() && data.node.data.isUserDefined) {
					if (!data.node.data.isSaving) {
						$j(data.node.span).find('> span.fancytree-icon')
						 	.removeClass('fancytree-icon').addClass('fancytree-custom-icon ui-icon ui-icon-pencil');
						$j(data.node.span).find('> span.fancytree-title').css('opacity', 1);
					} else {
						data.node.extraClasses = 'fancytree-statusnode-wait';
						$j(data.node.span).addClass(data.node.extraClasses);
						$j(data.node.span).find('> span.fancytree-title').css('opacity', 0.2);
					}
				}
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
							else {
								// delay the rendering of the node so that
								// the input has time to close
								setTimeout (function() {
									data.node.render(true);
									// sort the active branch after closing the input
									if(data.node.parent!=null) data.node.parent.sortChildren();
								}, 50);
							}
						}
					},
					beforeEdit: function(event, data) {
						// edit enabled only for user defined and non waiting nodes
						return data.node.data.isUserDefined && !data.node.data.isSaving;
						},
					edit: function (event,data) {
						data.input.select();
					},
					// Save data.input.val() or return false to keep editor open
					save: function (event, data) {
						// save only user defined nodes
						if (data.node.data.isUserDefined) {
							// get the new value
							var value = data.input.val().trim();
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
								// delay the rendering of the node so that the input
								// field can close and icon is not overwritten
								setTimeout(function(){
									data.node.data.isSaving = true;
									data.node.render(true);
								} , 100);
								// promise is handled in close callback
								savePromise = $j.ajax({
									type	:	'POST',
									url		:	'ajax/saveTerm.php',
									data	:	POSTdata,
									dataType:	'json'
								});
								return true;
							}
						}
						return false;
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
									} else if (JSONObj.status=='ERROR') {
										if (data.node.data.isNew) data.node.remove();
									}
									showHideDiv('',JSONObj.msg,JSONObj.status=='OK');
								} else {
									showHideDiv('',$j('#nodeSavingFailMsg').html(),false);
								}
							})
							.always (function() {
								savePromise=null;
								// delay the rendering of the node so that
								// it has time to be sorted by the done callback
								setTimeout (function() {
									data.node.data.isSaving = false;
									data.node.render(true);
									// sort the active branch after closing the input
									if(data.node.parent!=null) data.node.parent.sortChildren();
									clearFancyTreeFilter();
									if(data.node!=null) data.node.makeVisible({ noAnimation: true, scrollIntoView:false }); 
								}, 200);
							})
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
	var returnTree = initFancyTreeObj(element, false, options );

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
				deleteSelectedTreeNode(node,'check');
				break;
			default :
				showHideDiv ('Menu Click','Action not defined!',false);
			break;
			}
		}
	});

	initFancyTreeFilter();

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
		folder: false,
		isNew: true,
		isSaving: false,
		isUserDefined: true
	});
	// run the filter to show new node if a filter is active
	filterFancyTree();
	
	refNode.editStart();
}

/**
 * removes the selected node from the tree
 *
 * @param node
 */
function deleteSelectedTreeNode(node, op) {
	var POSTdata = {
			domaineRootNodeID : node.getParentList()[0].key,
			nodeID : node.key,
			op : op
		};

	$j.ajax({
		type	:	'POST',
		url		:	'ajax/deleteTerm.php',
		data	:	POSTdata,
		dataType:	'json'
	})
	.done (function(JSONObj){
		if (JSONObj) {
			// handle response status sent from server
			if (JSONObj.status=='FORCED') {
				// display force delete dialog
				$j('#cannot-delete-details').html(JSONObj.msg);
				
				var dialogButtons = {};
				var delButtonText = (JSONObj.delButtonText) ? JSONObj.delButtonText : i18n['confirm'];  

				dialogButtons[delButtonText] = function() {
					$j(this).dialog('close');
					// if user confirms, do the actual delete
					node.data.isSaving = true;
					node.render(true);
					deleteSelectedTreeNode (node, 'delete');
				};

				dialogButtons[i18n['cancel']] = function() {
					$j(this).dialog('close');
				};
				
				$j("#cannot-delete" ).dialog({
					resizable: false,
					dialogClass: 'no-close',
					width: '60%',
				    height:'auto',
				    modal: true,
				    buttons: dialogButtons
				});
			} else if (JSONObj.status=='CONFIRM') {
				// display ask confirm dialog
				var dialogButtons = {};

				dialogButtons[i18n['confirm']] = function() {
					$j(this).dialog('close');
					// if user confirms, do the actual delete
					node.data.isSaving = true;
					node.render(true);
					deleteSelectedTreeNode (node, 'delete');
				};

				dialogButtons[i18n['cancel']] = function() {
					$j(this).dialog('close');
				};

				$j('#ask-confirm-message').html(JSONObj.msg);
				$j("#ask-confirm-delete" ).dialog({
					resizable: false,
					dialogClass: 'no-close',
					width: '50%',
				    height:'auto',
				    modal: true,
				    buttons: dialogButtons
				});
			} else if (JSONObj.status=='ERROR') {
				// display error message
				showHideDiv('',JSONObj.msg, false);
			} else if (JSONObj.status=='OK') {
				// remove the node and display message
				node.remove();
				setTimeout( function(){ filterFancyTree(); }, 10);
				showHideDiv('',JSONObj.msg,true);
			} else {
				showHideDiv('',$j('#nodeDelFailMsg').html(),false);
			}
		} else {
			showHideDiv('',$j('#nodeDelFailMsg').html(),false);
		}
	})
	.fail (function(){
		if (node.data.isSaving) {
			node.data.isSaving=false;
			node.render(true);
		}
		showHideDiv('',$j('#nodeDelFailMsg').html(),false);
	});
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
function initFancyTreeObj (element, canEdit, extraOptions) {

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
		init : function() {
			/**
			 * if the options passed to initDoc contain one or more assetID,
			 * simulate the click on the first row, so that the row shall open
			 */
			if ($j('#assetsTable').length>0) {
				var firstRow = $j('#assetsTable').find('tbody tr').first()[0];
				if (opts.assetID && !dataTableObj.fnIsOpen(firstRow)) { 
					setTimeout (function(){ $j('.expandAssetButton').first().click(); }, 50); 
				}
			}
		},
		source: {
			url: 'ajax/getEurovocTree.php'
		},
	};

	var returnTree = element.fancytree($j.extend(options,extraOptions));
	initFancyTreeFilter();
	return returnTree;
}

/**
 * clear (aka reset) the FancyTree node filter
 */
function clearFancyTreeFilter() {
	// clear text field and tree filter
	$j("input#treeFilterInput").val("");
	fancyTreeObj.fancytree('getTree').clearFilter();
	// fix hidden and shown elements
	$j('span.fancytree-node').parents('li').css('display','block');
	// reset object as it previously was
	setFancyTreeObjSelection(getSelectedTreeNodesArray());
}

/**
 * Performs the actual FancyTree node filtering doing a case insensitive search
 * for titles containig passed text and checking if node is new so that a new
 * node will be visible even with an active filter
 *  
 * @param text the string to be filtered
 * @returns number of matched nodes
 */
function filterFancyTree(text) {

	var n = 0;
	
	if (typeof text == 'undefined') {
		text = ($j("input#treeFilterInput").length>0) ? $j("input#treeFilterInput").val().trim() : ''; 
	} else {
		text = text.trim();
	}
	
	if (text.length > 0) {
		var matchRegExp = new RegExp (text, 'i');
		
		// NOTE: second parameter is to filter leaves only
		n = fancyTreeObj.fancytree('getTree').filterNodes(function(node) {
					return matchRegExp.test(node.title) || node.data.isNew;
				}, false);
	}
	
	// if any matched nodes, expand them
	if (n>0) {
		fancyTreeObj.fancytree("getRootNode").visit(function(node){
			if (node.title.toLowerCase().indexOf(text.toLowerCase())>-1) {
				if (!node.isExpanded()) node.makeVisible({ noAnimation: true, scrollIntoView:false });
			}
		});
		// fix hidden and shown elements' css
		 $j('span.fancytree-hide').parents('li').css('display','none');
		 $j('span.fancytree-match').parents('li').css('display','block');
	}
	
	return n;
}

/**
 * inits the text input to filter the
 * fancyTree and the button to clear it
 */
function initFancyTreeFilter() {
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
				filterFancyTree(match);
				$j("button#resetTreeFilter").attr("disabled", false);
			}
		}).focus();
	}

	/**
	 * init clear button
	 */
	if ($j("button#resetTreeFilter").length>0) {
		$j("button#resetTreeFilter").click(function() {
			clearFancyTreeFilter();
		}).attr("disabled", true);
	}
}

/**
 * function to delete a source with an ajax call
 *
 * @param jqueryObj
 * @param id_source
 * @param message
 */
function deleteSource (jqueryObj, id_source, message) {
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_source.php',
			data	:	{ id: id_source },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
					if (JSONObj.status=='OK') {
						// deletes the corresponding row from the DOM with a fadeout effect
						jqueryObj.parents("tr").fadeOut("slow", function () {
							var pos = dataTableObj.fnGetPosition(this);
							dataTableObj.fnDeleteRow(pos);
							});
					}
					showHideDiv('', JSONObj.msg, JSONObj.status=='OK');
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

/**
 * Runs eurovoc tree export by making an ajax call
 * to the script that generate the zip file and
 * if no errors, starts the download
 */
var exportRunning = false;

function doExportEurovoc() {
	
	var theForm = $j("form[name='exporteurovoc']");
	
	if (!exportRunning && theForm.length>0) {
		
		var imgObj = $j('<img>');
		imgObj.attr ('style', 'vertical-align:middle;');
		imgObj.attr ('src', HTTP_ROOT_DIR + '/js/include/jquery/ui/images/ui-anim_basic_16x16.gif');
		
		$j.ajax({
			type	: 'POST',
			url		: 'ajax/doExportEurovoc.php',
			data	: $j(theForm).serialize(),
			beforeSend : function() {
				exportRunning = true;
				$j(theForm).find('select, button').attr('disabled',exportRunning);
				// show the spinner
				imgObj.appendTo($j('#eurovoc-exportButton').parents('li'));
				// show a notification pop up
				showHideDiv('', $j('#exportStartedMsg').html(), true);				
			},
			dataType:'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
					if (JSONObj.status=='OK') {
						document.location.href = 'downloadEurovoc.php?filename='+JSONObj.filename;
					}
				}
		})
		.always(function () { 
			exportRunning = false;
			$j(theForm).find('select, button').attr('disabled',exportRunning);
			imgObj.remove();
		})
		.fail(function () { showHideDiv('', 'Fatal export error', false); });
	}
}

/**
 * deletes all user defined DESCRIPTEURS from
 * Eurovoc database, together with asset associations
 */
var isResetting = false;
function doResetEurovoc(op) {
	if (isResetting) return;	
	isResetting = true;
	
	var POSTdata = {
			op : op
		};
	
	if (op=='delete') {
		var imgObj = $j('<img>');
		imgObj.attr ('style', 'vertical-align:middle;');
		imgObj.attr ('src', HTTP_ROOT_DIR + '/js/include/jquery/ui/images/ui-anim_basic_16x16.gif');
		imgObj.appendTo($j('#resetEurovocBtnContainer'));
	}

	$j.ajax({
		type	:	'POST',
		url		:	'ajax/resetEurovoc.php',
		data	:	POSTdata,
		dataType:	'json'
	})
	.done (function(JSONObj){
		if (JSONObj) {
			// handle response status sent from server
			if (JSONObj.status=='CONFIRM') {
				// display ask confirm dialog
				var dialogButtons = {};

				dialogButtons[i18n['confirm']] = function() {
					$j(this).dialog('close');
					// if user confirms, do the actual delete
					doResetEurovoc ('delete');
				};

				dialogButtons[i18n['cancel']] = function() {
					$j(this).dialog('close');
				};

				$j('#ask-confirm-message').html(JSONObj.msg);
				$j("#ask-confirm-delete" ).dialog({
					resizable: false,
					dialogClass: 'no-close',
					width: '50%',
				    height:'auto',
				    modal: true,
				    buttons: dialogButtons
				});
			} else if (JSONObj.status=='ERROR') {
				// display error message
				showHideDiv('',JSONObj.msg, false);
			} else if (JSONObj.status=='OK') {
				// reload fancyTreeObj
				if (fancyTreeObj!=null) fancyTreeObj.fancytree('getTree').reload();
				// display message
				showHideDiv('',JSONObj.msg,true);
			}
		}
	})
	.always(function(){ isResetting=false; if(op=='delete') imgObj.remove(); })
	.fail (function(xhr){
		showHideDiv('Server Error', xhr.responseText, false);
	});	
}

function updateSelect(what) {
	$j('#'+what).attr('disabled','disabled');
	
	var data = {
			what: what,
			typology: $j('#tipologia option:selected').val()
	};
	
	if (what=='categoria') {
		$j('#classe').attr('disabled','disabled');
		$j('#classe').selectric('refresh');
	} else if (what=='classe') {
		$j.extend(data,{
			category: $j('#categoria option:selected').val()
		});
	} else return;
	
	return $j.ajax({	
		type	:	'GET',
		url		:	'ajax/getSelectOptions.php',
		data	:	data,
		dataType:	'html',
		beforeSend : function() {			
			$j('#'+what).selectric('refresh');
		}
	}).done(function(html){
		$j('#'+what).html(html);		
	}).fail(function() {
		$j('#'+what).html('<option></option>');
	}).always (function() {
		$j('#'+what).removeAttr('disabled');
		$j('#'+what).val(0);
		$j('#'+what).selectric('refresh');
	});
}

function editAbrogated(assetID) {
	// ask the server for the edit abrogated form
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/edit_abrogated.php',
		data	:	{ assetID : assetID },
		dataType:	'json'
	})
	.done(function (JSONObj){
		if (JSONObj.status=='OK') {
			if (JSONObj.html && JSONObj.html.length>0) {
				// build the dialog
				var theDialog = $j('<div />').html(JSONObj.html).dialog( {
					title: JSONObj.dialogTitle,
					autoOpen: false,
					modal:true,
					resizable: false,
					width: '80%',
					show: {
						effect: "fade",
						easing: "easeInSine", 
						duration: 250
			        },
			        hide: {
						effect: "fade",
						easing: "easeOutSine", 
						duration: 250
			        },
			        open: function() {
			        	initButtons();
			        	initToolTips();
			        	// make the datepickers
			        	$j('#formAbrogatedTable td input.datepicker').datepicker({
			    			showOtherMonths: true
			    		});
			        }
				});
				
				// get and hide the submit button				
				var submitButton = theDialog.find('input[type="submit"]');
				submitButton.parents('p').hide();
				
				// dialog buttons array
				var dialogButtons = {};

				// confirm dialog button
				dialogButtons[i18n['confirm']] = function() {
					// get form (previously hidden) submit button onclick code
					var onClickDefaultAction = submitButton.attr('onclick');
					// execute it, to hava ADA's own form validator
					var okToSubmit = (onClickDefaultAction.length > 0) ? new Function(onClickDefaultAction)() : false;						
					// and if ok ajax-submit the form
					if (okToSubmit) {
						ajaxSubmitAbrogatedForm(theDialog.find('form').serialize());
						theDialog.dialog('close');
					}
				};
				
				// cancel dialog button
				dialogButtons[i18n['cancel']] = function() {
					theDialog.dialog('close');
				};
				
				// set the defined buttons
				theDialog.dialog( "option", "buttons", dialogButtons );
				
				// on dialog close, redraw the datatable and destroy dialog
				theDialog.on('dialogclose', function( event, ui){
					if (dataTableObj!=null) dataTableObj.fnDraw();
					$j(this).dialog('destroy').remove();
				});
				
				// on dialog enter keypress, call the confirm click
				theDialog.keypress(function(e) {
					if(e.which == 13) {
						e.preventDefault();
						theDialog.dialog("option","buttons")[i18n['confirm']]();
					}
				});
				
				// eventually open the dialog
				theDialog.dialog('open');
			}
		} else {
			if (JSONObj.msg) showHideDiv('', JSONObj.msg, false);
		}
	})
	.fail(function () { showHideDiv('', 'Server Error', false) } );
}

function ajaxSubmitAbrogatedForm(data) {
	// ask the server to save the abrogated array
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/edit_abrogated.php',
		data	:	data,
		dataType:	'json'
	})
	.done(function (JSONObj){
		if (JSONObj && JSONObj.status.length>0) {
			showHideDiv('', JSONObj.msg, JSONObj.status=='OK');
		}
	});
}

/**
 * function to delete an abrogation with an ajax call
 *
 * @param jqueryObj
 * @param abrogated_by
 * @param message
 */
function deleteAbrogation (jqueryObj, abrogated_by, message) {
	
	if ($j('#assetID').length<=0) return;
	
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{		
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_abrogated.php',
			data	:	{ abrogatedBy: abrogated_by,
						  assetID: $j('#assetID').val() },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
					if (JSONObj.status=='OK') {
						console.log(jqueryObj.parents('tr'));
						// deletes the corresponding row from the DOM with a fadeout effect
						jqueryObj.parents("tr").fadeOut("slow", function () {
							jqueryObj.parents("tr").remove();
						});
					}
					showHideDiv('', JSONObj.msg, JSONObj.status=='OK');
				}
		});
	}
}

/**
 * adds a row to the edit abrogation form, in the dialog
 */
function addAbrogatedRow() {
	// "clone" the last table row
	var outerRow = $j('#formAbrogatedTable tbody tr').last();
	var newRow = $j(outerRow).clone();
	
	// extract its number using a regexp match
	var regExp = /abrogato_da_(\d+)/;
	var match = regExp.exec(newRow.html());
	// add the row only if the regexp matched
	if (match!=null && match.length>1) {
		// get the number
		var number = parseInt(match[1]);
		// replace 'abrogato_da_'
		var abrogatoRegExp = new RegExp ('abrogato_da_'+number,'g');
		newRow.html( newRow.html().replace(abrogatoRegExp, 'abrogato_da_'+(number+1)));
		// replace 'data_abrogazione_'
		var abrogatoDataRegExp = new RegExp ('data_abrogazione_'+number,'g');
		newRow.html( newRow.html().replace(abrogatoDataRegExp, 'data_abrogazione_'+(number+1)));
		
		// add the newRow to the table
		$j('#formAbrogatedTable tbody:last').append(newRow);
    	// make the datepickers
    	$j('#formAbrogatedTable td input.datepicker').removeClass('hasDatepicker').datepicker({
			showOtherMonths: true
		});
	}
}