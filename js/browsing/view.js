/**
 * VIEW.JS
 *
 * @package		view
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Main view.php initializations, starts up tabbed content and nivo slider as
 * appropriate
 */
function initDoc() {
	// run script after document is ready
	$j(function() {
		
		// install flowplayer to an element with CSS class "ADAflowplayer"
		// generated by the media_viewing_classes if it's needed
		if ($j(".ADAflowplayer").length > 0)
			$j(".ADAflowplayer").flowplayer();

		// if there are tabs on the page, initialize'em
		if ($j("#tabs").length > 0) {
			$j("#tabs").tabs({
				collapsible : true,
				active : false
			});
			showTabs = true;
		}

		// if there's a nivo slider, start it
		if ($j("#slider").length > 0) {
			$j('#slider').nivoSlider({
				// effect: 'fade', // Specify sets like: 'fold,fade,sliceDown'
				// effect: 'fold', // Specify sets like: 'fold,fade,sliceDown'
				effect : 'slideInRight', // Specify sets like: 'fold,fade,sliceDown'
				slices : 15, // For slice animations
				boxCols : 8, // For box animations
				boxRows : 4, // For box animations
				animSpeed : 500, // Slide transition speed
				pauseTime : 3000, // How long each slide will show
				startSlide : 0, // Set starting Slide (0 index)
				directionNav : true, // Next & Prev navigation
				controlNav : true, // 1,2,3... navigation
				controlNavThumbs : true, // Use thumbnails for Control Nav
				pauseOnHover : true, // Stop animation while hovering
				manualAdvance : false, // Force manual transitions
				prevText : 'Prev', // Prev directionNav text
				nextText : 'Next', // Next directionNav text
				randomStart : false, // Start on a random slide
				beforeChange : function() {
				}, // Triggers before a slide transition
				afterChange : function() {
				}, // Triggers after a slide transition
				slideshowEnd : function() {
				}, // Triggers after all slides have been shown
				lastSlide : function() {
				}, // Triggers when last slide is shown
				afterLoad : function() {
				} // Triggers when slider has loaded
			}); // end nivoSlider init
		}
		
		
	}); // end $j function
} // end initDoc