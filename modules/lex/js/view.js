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

function initDoc (assetsArray, isSource) {
	loadAndDisplayAssets (assetsArray, isSource);
}

function loadAndDisplayAssets (assetsArray, isSource) {
	if (assetsArray) {
		$j.ajax({
			type	:	'GET',
			url		:	'ajax/getAssetDetails.php',			
			data	:	{ isSource: (isSource ? 1:0), assetID: assetsArray.shift() },
			dataType:	'json'
		}).done (function(JSONObj){
			if (JSONObj && JSONObj.status=='OK' && JSONObj.html.length>0 && JSONObj.assetID) {
				var innerHTML = $j(JSONObj.html).first('div').attr('id',JSONObj.assetID).hide();
				$j(innerHTML).html('<div>'+$j(innerHTML).html()+'</div>');
				// generate the title h3 if it's been returned
				var hasTitle = false;
				if (JSONObj.title && JSONObj.title.length>0) {
					$j(innerHTML).prepend($j('<h3/>').html(JSONObj.title).addClass('assetTitle'));
					hasTitle = true;
				}				
				// append asset text (and title) to main div with a fadeIn effect
				$j('#assetDetailsContainer').append(innerHTML);
				if (hasTitle) doAccordion('#'+JSONObj.assetID);
				$j('#'+JSONObj.assetID).fadeIn();				
			}
		}).always(function (JSONObj) {
			 if (assetsArray.length>0) loadAndDisplayAssets(assetsArray, isSource);
		});
	}
}

/**
 * renders the passed element as an accordion:
 * all the <h3> becomes clickable accordion header and the
 * div next to the <h3> becomes the collapsible content
 * 
 * @param elementID the element to make accordion
 */
function doAccordion(elementID) {
	$j(elementID).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
	  .find("h3")
	    .addClass("ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ui-corner-bottom")
	    .hover(function() { $j(this).toggleClass("ui-state-hover"); })
	    .prepend('<span class="ui-icon ui-icon-triangle-1-s"></span>')
	    .click(function() {
	      $j(this)
	        .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
	        .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
	        .next().toggleClass("ui-accordion-content-active").slideToggle();
	      return false;
	    })
	    .next()
	      .addClass("ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom")
	      .show();
}

function copyAssetInMyLog() {
    asset = $j('.assetDetail').html();
    okCopy = true;
    if (asset.length>0) {

            var waitElement = $j('<span> Copying...</span>');

            $j.ajax({
                    type	:	'GET',
                    url		:	'ajax/copyToMyLog.php',
                    data	:	{ asset: asset },
                    dataType:	'json',
                    beforeSend : function() { 
                            $j('.assetTitle').append(waitElement);
                            }
            }).done(function (JSONObj){
//                    if (JSONObj && JSONObj.status=='OK') {
//                            // set the returned user type
//                            // display the returned message
//                            if (JSONObj.msg) {
//                                    $j('<span class="suggestedUserType">'+JSONObj.msg+'</span>').insertAfter('#codice_fiscale');
//                            }				
//                    }
            }).always(function(JSONObj) {
                      $activeElement = $j('li.active');
//                      $( "ul li" ).filter( ".current" );
                      $activeElement.removeClass('active');
                      $j(waitElement).remove();
                      showHideDiv("" ,JSONObj.msg,JSONObj.status);
            });
    }
}
