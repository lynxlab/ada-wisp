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

// common options for peke uploader
var commonPekeOptions = {
		allowedExtensions : "zip",
		btnText : "Sfoglia Files..",
		field : 'uploaded_file',
		url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php',
		onFileError: function(file,error) { fileError = true; }
};

// the eurovoc fancytree object
var eurovocTree = null;

// the sources data table
var sourcesTable = null;

function initDoc(maxSize, userId) {
	
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
		// make a button
		$j('#nuova_tipologia_btn').button();
		// make a button
		$j('#nuova_fonte_btn').button();
		
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
		
		// autocomplete feature with cache for numero_fonte and titolo_fonte fields
//		var cache = {};
		var fieldName = null;
		
		$j('#numero_fonte, #titolo_fonte').autocomplete({
			minLength: 2,
			search: function( event, ui ) {
				// set the field name before asking for source
				fieldName = $j(event.target).attr('id').replace('_fonte','');
			},
			source: function( request, response ) {
//				var term = request.term;
//				if ( term in cache ) {
//					response( cache[ term ] );
//					return;
//				}
				
				if (fieldName != null) { 
					// term is already in the request,
					// add tableName and fieldName
					request = $j.extend ({
						tableName : 'fonti',
						fieldName : fieldName
					}, request);
					
					$j.getJSON( HTTP_ROOT_DIR+"/modules/lex/ajax/autocomplete.php", request, function( data, status, xhr ) {
//						cache[ term ] = data;
						response( data );
						fieldName = null;
						});
				}
			}
		});
		
		if ($j('#selectEurovocTerms').length>0) eurovocTree = initEurovocTree ($j('#selectEurovocTerms'),null);
		if ($j('#sourcesTable').length>0) sourcesTable = initDataTables($j('#sourcesTable'));
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
 * shows the add new fonte button
 */
function showAddNewButton() {
	$j('#nuova_fonte_btn').css('display','block');
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

function initDataTables(element) {
	return element.dataTable( {
				"aaSorting": [[ 1, "desc" ]],
		 		"bJQueryUI": true,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
                "bPaginate" : true,
                "aoColumns": [
                              // first empty column generated by ADA HTML engine, let's hide it
                              { "bSearchable": false,
                            	"bVisible":    false },
                              null,
                              null,
                              { "sType": "date-eu" },
                              null,
                              { "bSearchable" : false, "bSortable" : false, "sWidth" : "15%"}
                             ],
				"oLanguage": {
	                "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
	            }
	}).show();
}

function initEurovocTree (element, selectOnLoad) {
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
            var s = data.tree.getSelectedNodes().join(", ");
            $j("#echoSelection").text(s);
            
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
      	  
      	  	console.log (targetNodes.length);
      	  
//      	  console.log(isSelected);
//      	  console.log(data);
            
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
