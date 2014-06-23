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
//the fancytree object
var fancyTreeObj = null;
// the datatable object
var dataTableObj = null;
// the selected row ID
var selectedRowID = null;
// initial asset table width
var tableWidth = null;
// common options for peke uploader
var commonPekeOptions = {
		allowedExtensions : "zip",
		btnText : "Sfoglia Files..",
		field : 'uploaded_file',
		url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php',
		onFileError: function(file,error) { fileError = true; }
};

function initDoc(maxSize, userId, canEdit) {
	
	$j(document).ready(function() {
		// init the tabs
		$j('#lexmenu').tabs();

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
		if ($j('.assetTableContainer').length>0) tableWidth = parseInt($j('.assetTableContainer').width());
		// for performance reason, it's better to load the fancyTreeObj as the last element
		if ($j('#selectEurovocTerms').length>0) fancyTreeObj = initfancyTreeObj ($j('#selectEurovocTerms'),null);
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
		if (node.isExpanded()) node.setExpanded(false);
		if (node.isSelected()) node.setSelected(false);
		
		// if the current node key is in the passed array
		// selected and make visible the current node
        if ($j.inArray(parseInt(node.key), selectedNodes)>-1) {
        	if (!node.isSelected()) node.setSelected(true);
        	if (!node.isExpanded()) node.makeVisible({ scrollIntoView:false });
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
	
	var clearSelection = (selectedNodes==null); 
	
	// set the selection
    setFancyTreeObjSelection(selectedNodes);
	
	var speed = 350;
	var treeWidth = parseInt($j('.assetTreeContainer').width());
	
	if (!$j('.assetTreeContainer').is(':visible')) {
		// if tree is not visible, show it with an animation
		finalWidth = tableWidth - treeWidth - 10;
		$j('.assetTableContainer').animate({ width: finalWidth }, speed, function() {
			$j('.assetTreeContainer').show('slide',speed/2);
		} );
	} else if (clearSelection) {
		// if selected row was clicked, hide the tree with an animation
		$j('.assetTreeContainer').hide('slide',speed/2, function() {
			$j('.assetTableContainer').animate({ width: tableWidth-10 },speed);
		} );
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
	
	if ($j('.showTreeButton').length>0) {
		$j('.showTreeButton').button({
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
		params = {	
			aoColumns : [
		                      // first empty column generated by ADA HTML engine, let's hide it
		                      { "bSearchable": false, "bVisible":    false },
		                      { "sWidth" : "9%" } ,
		                      null,
		                      { "sType": "date-eu", "sWidth" : "15%" },
		                      { "sWidth" : "20%" },
		                      { "sClass": "noteditable", "bSearchable" : false, "bSortable" : false, "sWidth" : "8%" }
		                     ],
			sAjaxSource : 'ajax/getSourcesTableData.php'
		};
	} else if (theID == 'assetsTable') {
		// get the source id
		var sourceID = parseInt($j('[id^=assetsContainer_]').attr('id').replace(/^.*?_/, ''));
		
		if (!isNaN(sourceID)) {
			params = {
				aoColumns : [
			                      // first empty column generated by ADA HTML engine, let's hide it
			                      { "sClass": "center noteditable expandAsset", "bSortable": false, "bSearchable" : false },
			                      { "bSearchable": false, "bVisible":    false },
			                      { "sWidth" : "37%" } ,
			                      { "sWidth" : "32%" } ,
			                      { "sType": "date-eu", "sWidth" : "12%" },
			                      { "sType": "date-eu", "sWidth" : "12%" },
			                      { "bSearchable" : false, "bSortable" : true, "sWidth" : "7%" },
			                     ],
				sAjaxSource : 'ajax/getAssetsTableData.php',
				"fnServerParams": function ( aoData ) {
				      aoData.push( { "name": "sourceID", "value": sourceID });
				},
			};
		}
	}
	
	if (params!=null) {
		oTable = element.dataTable(
    		$j.extend({} , params,
    				{
						"aaSorting": [[ 1, "desc" ]],
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
										if ($j(this).attr('id')==selectedRowID)
											{
												$j(this).addClass('selectedRow');
												// prepare parameters for openAssetDetails
												assetID = parseInt($j(this).attr('id').replace(/^.*?:/, ''));
												imgObj = $j(this).find('td img.expandAssetButton')[0];
												openAssetDetails(assetID, this, imgObj);
											}
											
									});
								}
								
								if (!canEdit) return;
								
								$j('#'+element.attr('id')+' tbody td').not('.noteditable').editable( 'ajax/updateRow.php', {
									"placeholder" : "",
									"callback": 
										function( sValue, y ) {
										/* Redraw the table from the new data on the server */
										if (typeof oTable != 'undefined') oTable.fnDraw();
										},
									"height": "14px"
								} );
							}
    				})
		).show();		
	}
	
	return oTable;
}

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

function initfancyTreeObj (element, selectOnLoad) {
    return element.fancytree({
    	extensions: ["childcounter"],
        childcounter: {
            deep: false,
            hideZeros: true,
            hideExpanded: true
          },
        debugLevel: 0,
        checkbox: true,
        selectMode: 2,
        select: function(event, data) {
            // Display list of selected nodes
//            var s = data.tree.getSelectedNodes().join(", ");
//            $j("#echoSelection").text(s);
//            
//            var isSelected = data.node.isSelected();
//      	  	var nodeKey = data.node.key;
//      	  	
//      	  	root = $j(this).fancytree("getTree").rootNode;
//        
//      	  	var targetNodes = root.findAll(function(node){
//      	  		return node.key==nodeKey;
//      	  	});
//      	  
//      	  	if (targetNodes.length>1) {
//      	  		for (var i=0; i<targetNodes.length; i++) {
//      	  			targetNodes[i].setSelected(isSelected);
//      	  		}
//      	  	}           
          },
          source: {
            url: 'ajax/getEurovocTree.php'
          },
          // make selected nodes visible on init
          init: function (event, data) {
        	  var s = data.tree.getSelectedNodes();
        	  for (var i=0; i<s.length; i++) {
        		  s[i].makeVisible();
        	  }
              $j("#echoSelection").text(s.join(", "));
          }
      });
}

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
