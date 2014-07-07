<?php
/**
 * Holis Search Management Class
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
 * @version		   0.1
 */

/**
 * class for managing Holis Search
 *
 * @author giorgio
 */

require_once MODULES_HOLISSEARCH_PATH . '/include/form/formIndexSearch.php';

class holisSearchManagement
{
	
	public function index() {
		/* @var $html string holds html code to be retuned */
		$htmlObj = new FormIndexSearch();
		/* @var $path   string  path var to render in the help message */
		$help = translateFN('Benvenuto nella Ricerca Holis/SESPIUS');
		/* @var $status string status var to render in the breadcrumbs */
		$title= translateFN('Ricerca');
		
		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
	}
	
	public function runSearch($searchtext, $searchCourseCount=0) {
		/* @var $html string holds html code to be retuned */
		$htmlObj = CDOMElement::create('div','id:searchResults');
		/* @var $path   string  path var to render in the help message */
		$help = translateFN('Risultati della Ricerca Holis/SESPIUS');
		/* @var $status string status var to render in the breadcrumbs */
		$title= translateFN('Ricerca');
		
		/**
         * clean up the querystring
		 */
		$cleantext = $this->cleanSearchText($searchtext);
		
		/**
         * user entered query string echo, it's safe to remove this
		 */
		$youSearched = CDOMElement::create('span','class:yousearched');
		$youSearched->addChild (new CText(translateFN('Hai cercato').': '.$searchtext));
		
		/**
		 * DO NOT REMOVE THIS span, it's needed
		 * by the javascript to have the $cleantext
		 */
		$termSpan = CDOMElement::create('span','id:searchtext');
		$termSpan->setAttribute('style', 'display:none');
		$termSpan->addChild(new CText($cleantext));
		
		/**
		 * DO NOT REMOVE THIS span, it's needed
		 * by the javascript to have the $querystring (aka searchtext)
		 */
		$querySpan = CDOMElement::create('span','id:querystring');
		$querySpan->setAttribute('style', 'display:none');
		$querySpan->addChild(new CText($searchtext));
		
		
		/**
         * add a span for the translated text to display
         * when waiting to receive searched terms taxonomy 
		 */
		$taxonomyWaitText = CDOMElement::create('span','id:taxonomyWaitText');
		$taxonomyWaitText->setAttribute('style', 'display:none');
		$taxonomyWaitText->addChild (new CText(translateFN('Cerco i sinonimi dei termini di ricerca').'...'));
		
		/**
         * display cleant text, it's safe to remove this span
		 */
		$termSearched = CDOMElement::create('span');
		$termSearched->addChild (new CText('Termini di ricerca: '.$cleantext));
		
		$resultsWrapper = CDOMElement::create('div','id:resultsWrapper');
		
		/**
		 * prepare an hidden div for no results display
		 */ 
		$noResults = CDOMElement::create('div','class:noResults');
		$noResults->setAttribute('style', 'display:none');
		$noResults->addChild(new CText(translateFN('Nessun risultato trovato')));
		
		if (MODULES_LEX) {			
			/**
			 * prepare div to hold modules/lex search results
			 */
			$lexDIV = CDOMElement::create('div','id:moduleLexResults');
			
			/**
			 * add a span for the translated text to display
			 * when waiting to receive module lex search result
			 */
			$lexSearchWaitText = CDOMElement::create('span','id:lexSearchWaitText');
			$lexSearchWaitText->setAttribute('style', 'display:none');
			$lexSearchWaitText->addChild (new CText(translateFN('Cerco tra le fonti legislative').'...'));
			
			$lexTitle = CDOMElement::create('h2','id:moduleLexResultsTitle');
			$lexTitle->addChild (new CText(translateFN('Ricerca Fonti')));
			$lexDIV->addChild ($lexTitle);
			
			// add a noresults div to the $lexDIV
			$clone = clone $noResults;
			$clone->setAttribute('id', 'noResultsmoduleLex');
			
			$lexDIV->addChild($lexSearchWaitText);
			$lexDIV->addChild($clone);
			
			$resultsWrapper->addChild($lexDIV);						
		}
		
		$nodesDIV = CDOMElement::create('div','id:nodeResults');
		
		$nodesTitle = CDOMElement::create('h2','id:nodeResultsTitle');
		$nodesTitle->addChild (new CText(translateFN('Ricerca nodi')));
		$nodesDIV->addChild ($nodesTitle);
		
		// add a div for each course to be search, to be filled by javascript
		for ($i=0; $i<$searchCourseCount; $i++) {
			$nodesDIV->addChild(CDOMElement::create('div','id:nodeResult:'.$i.',class:nodeResult'));			
		}
		// add a noresults div to the $nodesDIV
		$clone = clone $noResults;
		$clone->setAttribute('id', 'noResultsNode');
		$nodesDIV->addChild($clone);
		
		$resultsWrapper->addChild($nodesDIV);
				
		$htmlObj->addChild ($termSpan); // do not remove, see above
		$htmlObj->addChild ($querySpan); // do not remove, see above
		$htmlObj->addChild ($taxonomyWaitText);
		$htmlObj->addChild ($youSearched);
		$htmlObj->addChild ($termSearched);
		$htmlObj->addChild (CDOMElement::create('div','class:clearfix'));
		$htmlObj->addChild ($resultsWrapper);
		
		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
	}
	
	/**
	 * cleans the search text from punctuation and stopwords
	 * 
	 * @param string $searchtext
	 * 
	 * @return string
	 * 
	 * @access private
	 */
	private function cleanSearchText($searchtext) {
		/**
		 * punctuation signs to be removed from
		 * searchtext, add as many as you like
		 */
		$punctuation = array ( '.',',',':',';','?','!','-' );
		
		$returntext = strtolower($searchtext);
		$returntext = str_replace($punctuation, '', $returntext);
		
		$stopwords = AMAHolisSearchDataHandler::getStopWordsArray();
		
		if (count($stopwords)>0) {
			foreach ($stopwords as $stopword) {
				$regExpstopwords[] = '/\b'.preg_quote($stopword).'\b/i';
			}
			$returntext = preg_replace($regExpstopwords, '', $returntext);
		}
		// clean empty and unwanted spaces
		$returntext = trim($returntext);
		$returntext = preg_replace('/\s+/', ' ', $returntext);

		return $returntext;
	}
	
} // class ends here