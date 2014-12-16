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
	private $_typologiesArr = null;
	private $_forceAbrogated = false;
	
	public function __construct($forceAbrogated, $noTypology) {
		if (MODULES_LEX && !$noTypology) {
			// load typologies from the AMALexDataHandler
			require_once MODULES_LEX_PATH.'/include/AMALexDataHandler.inc.php';
			$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
			if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
			$this->_typologiesArr = AMALexDataHandler::instance(MultiPort::getDSN($pointer))->getTypologies();
			if (AMA_DB::isError($this->_typologiesArr)) $this->_typologiesArr = null;
		}
		
		$this->_forceAbrogated = $forceAbrogated;
	}
	
	public function index() {
		return $this->runSearch(null);
	}
	
	public function runSearch($searchtext, $searchCourseCount=0) {
		/* @var $html string holds html code to be retuned */
		$htmlObj = CDOMElement::create('div','id:searchResults');
		/* @var $path   string  path var to render in the help message */
		$help = translateFN('Risultati della Ricerca Holis/SESPIUS');
		if ($this->_forceAbrogated) $help .= ' - '.translateFN('Ricerca svolta sui soli asset abrogati');
		/* @var $status string status var to render in the breadcrumbs */
		$title= translateFN('Ricerca');
		
		/**
         * clean up the querystring
		 */
		$cleantext = $this->cleanSearchText($searchtext);
		
		/**
		 * search form
		 */
		$formdata['searchtext'] = $searchtext;
		if (MODULES_LEX && !is_null($this->_typologiesArr)) {
			$formdata['typologiesArr'] = $this->_typologiesArr;
			$formdata['tipologia'] = null;
			$formdata['categoria'] = null;
			$formdata['classe'] = null;
			
			// set typology
			if (isset($_GET['tipologia']) && strlen(trim($_GET['tipologia']))>0 && trim($_GET['tipologia'])!=='null')
				$formdata['tipologia'] = trim($_GET['tipologia']);
			// set category
			if (isset($_GET['categoria']) && strlen(trim($_GET['categoria']))>0 && trim($_GET['categoria'])!=='null')
				$formdata['categoria'] = trim($_GET['categoria']);
			// set class
			if (isset($_GET['classe']) && strlen(trim($_GET['classe']))>0 && trim($_GET['classe'])!=='null')
				$formdata['classe'] = trim($_GET['classe']);
			// set abrogated
			if (!is_null($searchtext)) {
				if (!$this->_forceAbrogated && isset($_GET['abrogato']) && is_numeric($_GET['abrogato']))
					$formdata['abrogato'] = intval($_GET['abrogato']);
				else
					$formdata['abrogato'] = 1;
			} else $formdata['abrogato'] = null;
			
			/**
			 * DO NOT REMOVE THIS span, it's needed
			 * by the javascript to have the the complete typology id
			 */
			require_once MODULES_LEX_PATH. '/include/management/sourceTypologyManagement.inc.php';
			$tripleID = sourceTypologyManagement::getIDFromTriple($formdata['tipologia'], $formdata['categoria'], $formdata['classe']);
			
			$tripleSpan = CDOMElement::create('span','id:tripleID');
			$tripleSpan->setAttribute('style', 'display:none');
			$tripleSpan->addChild(new CText(intval($tripleID)));
		}
		$searchForm = new FormIndexSearch($formdata, $this->_forceAbrogated);

		if (!is_null($searchtext)) {
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
			if (isset($tripleSpan)) $htmlObj->addChild($tripleSpan); // do not remove, see above
			$htmlObj->addChild (new CText($searchForm->getHtml()));
			$htmlObj->addChild ($taxonomyWaitText);
			$htmlObj->addChild (CDOMElement::create('div','class:clearfix'));
			$htmlObj->addChild ($resultsWrapper);
		} else {
			/**
			 * no serachtext, display the search form only
			 */
			$htmlObj->addChild (new CText($searchForm->getHtml()));
		}
		
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