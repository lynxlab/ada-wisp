/**
 * HOLISSEARCH MODULE.
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
 * @version		   0.1
 */

var wordSeparator = ' ';

function initDoc() {
	var hsm = new HolisSearchManagement();
	
	if (typeof arguments[0]!='undefined') {
		/**
		 * we've been asked to perform a search
		 * arguments[0] is the courseID array
		 * arguments[1] tells if modules lex must be searched
		 * 
		 */
		hsm.doSearch(arguments[0],arguments[1]);
	}
}

/**
 * inits the tooltips in all the children of 
 * passed element having the .tooltip class
 */
function initToolTips(elementID) {
	// inizializzo i tooltip sul title di ogni elemento!
	if ($j(elementID + ' .tooltip').length>0) {
		$j(elementID + ' .tooltip').tooltip(
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
						my : "left bottom-5",
						at : "left top"
					}
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

/**
 * HolisSearchManagement main class
 */
var HolisSearchManagement = (function() {
	function HolisSearchManagement() {
		// true if modules lex must be searched
		this.hasModuleLex = false;
		// main progressbar and label
		this.progressbar = null;
		this.progressLabel = null;
		// text do be display on no results found
		this.showNoResultsNode = true;
		this.showNoResultsSources = true;
		// array of id courses to be searched
		this.searchCoursesIDs = Array();
		// array of terms to be searched
		this.searchTermsArray = Array();
	};
	
	/**
	 * inits the main progress bar and the lex progress bar if needed
	 */
	var _initProgressBar = function () {
		
		$j('#resultsWrapper').prepend('<div id="progressbar" style="display:none;"><div class="progress-label">'+$j('#taxonomyWaitText').text()+'</div></div>');	

		this.progressbar = $j( "#progressbar" ),
		this.progressLabel = $j( ".progress-label" );
		
		var thisReference = this;

		this.progressbar = this.progressbar.progressbar({
	      value: false,
	      max: this.searchCoursesIDs.length,
	      change: function() {
	    	  thisReference.progressLabel.text( parseInt((thisReference.progressbar.progressbar( "value" )/thisReference.progressbar.progressbar( "option", "max" ))*100) + "%" );
	      },
	      complete: function() {
	    	  thisReference.progressbar.fadeOut( function(){
	    		  // remove progress bar
	    		  thisReference.progressbar.remove();
	    		  // show no results text if needed
				  if (thisReference.showNoResultsNode) $j('#noResultsNode').fadeIn();
				  // show module lex progress bar if needed
				  if (thisReference.hasModuleLex && $j('#lex-progressbar').length>0) $j('#lex-progressbar').fadeIn();
	        });
	      }
	    });
		
		// if hasModuleLex add another progress bar
		if (this.hasModuleLex) {
			$j('#resultsWrapper').prepend('<div id="lex-progressbar" style="display:none;"><div class="progress-label">'+$j('#lexSearchWaitText').text()+'</div></div>');
			$j('#lex-progressbar').progressbar({ value: false });
		}
		
	    return this.progressbar.fadeIn();
	}; // ends _initProgressBar
	
	/**
	 * initialize the search array with the terms returned from the
	 * server via an ajax call to the php responsible for querying the
	 * multi word net web service or the taxonomy web service
	 */
	var _initSearchArray = function() {
		this.searchTermsArray = $j('#searchtext').text().split(wordSeparator);
		
	    return $j.ajax({
				type	:	'POST',
				url		:	'ajax/getSearchTerms.php',
				data	:	{ searchTerms: this.searchTermsArray },
				dataType:	'json'
		});
	}; // ends _initSearchArray
	
	/**
	 * run the search on modules lex, and builds the UI upon data arrival
	 */
	var _runModuleLexSearch = function() {
		
    	$j.ajax({
			type	:	'POST',
			url		:	'ajax/getSearchModuleLex.php',
			data	:	{ searchTerms: this.searchTermsArray,
						  searchtext: $j('#searchtext').text(),
						  querystring: $j('#querystring').text() },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj && JSONObj.data!=null) {
				if (JSONObj.status=='OK') {
					$j('#moduleLexResults').append($j('<div>'+JSONObj.data+'</div>'));
					$j('.moduleLexResult').fadeIn();
					
					$j('table.moduleLexResultsTable').dataTable({
						"aaSorting": [[ 1, "desc" ]],
						"oLanguage": {
				            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
				        },
				        "fnInitComplete": function(settings, json) {
				        	// reset dataTables_wrapper classes that were removed by dataTable
				            $j(this).closest('.dataTables_wrapper').addClass('ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom');
				          }
					});
					
					doAccordion('.moduleLexResult');
					initToolTips('.moduleLexResult');
				} else {
					/**
					 * log the error message to the conosle
					 */
					console.log (JSONObj.data);					
				}
			}
		})
		.always (function (JSONObj){
			// remove progress bar
			if ($j('#lex-progressbar').length>0) $j('#lex-progressbar').remove();
			// show no results text if needed
			if (!JSONObj || JSONObj.data==null || JSONObj.data=='' || JSONObj.status!='OK') {
				$j('#noResultsmoduleLex').fadeIn();
			}
		});
	};
	
	/**
	 * run the search in ADA courses main loop.
	 * Fires an ajax request for each course id and build
	 * the UI with animations upon data arrival
	 */
	var _runAjaxSearchLoop = function() {
		this.progressbar.progressbar( "value", 0 );
		
		var thisReference = this;
		
	    for (var i=0; i < this.searchCoursesIDs.length; i++) {
	    	
	    	$j.ajax({
				type	:	'POST',
				url		:	'ajax/getSearchNodesTableData.php',
				data	:	{ courseID: this.searchCoursesIDs[i],
							  searchTerms: this.searchTermsArray,
							  position:i,
							  searchtext: $j('#searchtext').text() },
				dataType:	'json'
			})
			.done  (function (JSONObj) {
				
				if (JSONObj && JSONObj.data!=null) {
						if (JSONObj.status=='OK') {
							thisReference.showNoResultsNode = false;
							var resultObj = $j('<div>'+JSONObj.data+'</div>').hide();
							var animateTarget = '';
							
							/**
							 * if the PHP has set up an empty div
							 * for us, then fill it! else append
							 * to the container div
							 */
							if ($j('#nodeResult\\:'+JSONObj.position).length>0) {								
								$j('#nodeResult\\:'+JSONObj.position).html(resultObj);
								animateTarget = '#nodeResult\\:'+JSONObj.position;
							} 
							else {
								$j('#nodeResults').append(resultObj);
								animateTarget = '#nodeResults'; 
							}
							
							/**
							 * show the target div if it's hidden
							 */
							if (!$j(animateTarget).is(':visible')) $j(animateTarget).show();
							
							/**
							 * animate height, update progressbar value and fade in results
							 */
							$j(animateTarget).animate({
								 height: '+=' + parseInt($j(resultObj).outerHeight(true))
							}, function() {
								// fade in results
								$j(animateTarget+' table.nodesResultsTable').dataTable({
									"aaSorting": [[ 2, "desc" ]],
									"oLanguage": {
							            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
							        },
							        "fnInitComplete": function(settings, json) {
							        	// reset dataTables_wrapper classes that were removed by dataTable
							            $j(animateTarget+' .dataTables_wrapper').addClass('ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom');
							          }
								});
								doAccordion(animateTarget);
								initToolTips(animateTarget);
								
								resultObj.fadeIn( function (){
									// reset target height to auto so that
									// the height will follow accordion open/close
									$j(animateTarget).css({ "height" : "auto" });
								} );
							});
						} else {
							/**
							 * log the error message to the conosle
							 */
							console.log (JSONObj.data);
						}
					}
			}).always(function() {
				thisReference.progressbar.progressbar( "value", 
						parseInt(thisReference.progressbar.progressbar( "value")) +1 );
			});
	    } // ends loop
	}; // ends _runAjaxSearchLoop

/**
 * runs the needed initializations and searches
 * 
 * @param searchCoursesIDs array of course ids to be searched
 * @param hasModuleLex true if module lex must be searched as well
 */	
HolisSearchManagement.prototype.doSearch = function(searchCoursesIDs, hasModuleLex) {
	// set courses to search
	this.searchCoursesIDs = searchCoursesIDs;
	// set the boolean to search the lex module
	this.hasModuleLex = hasModuleLex;	
	// reference of this to make it visible to done function
	var thisReference = this;
	
	// set visibility of module lex search results if needed,
	// else remove it from the DOM
	if (this.hasModuleLex) $j('#moduleLexResults').show();
	else $j('#moduleLexResults').remove();

	// when the progress bar has done its initialization
	$j.when( _initProgressBar.call(this) ).done( function() {
		// init the search array with an ajax call to the server
		// and when the ajax call has finished (aka sync call)
		$j.when(_initSearchArray.call(thisReference)).done ( function (returnedObj) {
			// set the searchTermsArray to the returned JSON array
			thisReference.searchTermsArray = returnedObj;
			
			/**
			 * REMOVE THESE 2 LINES IN PRODUCTION
			 */
			console.log (thisReference.searchTermsArray.length + ' terms searched:');
			console.log (thisReference.searchTermsArray);
			
			// start the module lex search if needed
			if (thisReference.hasModuleLex) _runModuleLexSearch.call(thisReference);
			
			// and run the loop the fires an ajax call for each course
			// and when the loop has finished show the no results div if needed
			_runAjaxSearchLoop.call(thisReference);
		});
	});
}; // ends doSearch

	return HolisSearchManagement;
})(); // JS class ends here