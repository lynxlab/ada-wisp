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
		onFileError: function(file,error) { fileError = true; }
};

function initDoc(maxSize, userId) {
	
	$j(document).ready(function() {
		// init the tabs
		$j('#lexmenu').tabs();

		// sets maximum upload file size
		commonPekeOptions.maxSize = maxSize;
		
		/**
		 * set javaScript file upload handler
		 * on file upload success for eurovoc file upload
		 */
		$j('#importfile-eurovoc').pekeUpload($j.extend ({
			url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php?userId='+userId+'&fieldUploadName='+$j(this).attr('id'),
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
			url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php?userId='+userId+'&fieldUploadName='+$j(this).attr('id'),
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
