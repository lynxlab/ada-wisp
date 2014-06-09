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

function initDoc(maxSize, userId)
{
	$j(document).ready(function() {
		$j( "#lexmenu" ).tabs();

		commonPekeOptions.maxSize = maxSize;
		
		/**
		 * set javaScript file upload handler
		 * on file upload success for eurovoc file upload
		 */
		$j("#importfile-eurovoc").pekeUpload($j.extend ({
			url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php?userId='+userId+'&fieldUploadName='+$j(this).attr('id'),
			onFileSuccess : function(file) {
				 if (!fileError) doImportEurovoc(file);
				 fileError = false;
			}
		} , commonPekeOptions));
		
		/**
		 * set javaScript file upload handler
		 * on file upload success for jex file upload
		 */
		$j("#importfile-jex").pekeUpload($j.extend ({
			url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php?userId='+userId+'&fieldUploadName='+$j(this).attr('id'),
			onFileSuccess : function(file) {
				if (!fileError) alert(file);
				fileError = false;
			}
		} , commonPekeOptions));

		progressbar = $j("#progressbar");
		progressLabel = $j("#progress-label");

		progressbar.progressbar({
			value : 0,
			max	  : 1,
			change : function() {
				progressLabel.text(progressbar.progressbar("value") + " / " + progressbar.progressbar("option","max"));
			},
			complete : function() {
				progressLabel.text(progressbar.progressbar("option","max") + " / " + progressbar.progressbar("option","max"));
			}
		});
	});
}

function doImportEurovoc(file) {
	
	var theForm = $j("form[name='eurovoc']");	
	theForm.attr("target","eurovocResults");
	
	/**
	 * hide upload button and progress bar,
	 * when complete show output iframe and
	 * when complete submit the form
	 */
	theForm.slideUp(500, function() {
		$j("#eurovocResults").slideDown(500, function (){
			theForm.submit();			
		});
	});

}
