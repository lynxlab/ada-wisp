<?php
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

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling file upload module form
 *
 * @author giorgio
 */
class FormIndexSearch extends FForm {

	public function __construct($data=array(), $forceAbrogated=false) {
		parent::__construct();
		$this->setName('searchForm');
		$this->setId('searchForm');
		$this->setMethod('GET');
		
		$label = (isset($data['searchtext']) && strlen($data['searchtext'])>0) ? translateFN('Hai cercato').': ' : translateFN('Cosa stai cercando ?') ;
		
		$searchText = FormControl::create(FormControl::INPUT_TEXT, 's', $label); //->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		
		if (isset($data['searchtext']) && strlen($data['searchtext'])>0) {			
			$searchText->withData(htmlentities($data['searchtext'], ENT_COMPAT | ENT_HTML401, ADA_CHARSET));
		}
		
                $searchTypeSelAr = array(
                    HOLIS_SEARCH_FILTER=>translateFN('Filtro'),
                    HOLIS_SEARCH_CONCEPT=>translateFN('Per concetti'),
                    HOLIS_SEARCH_EUROVOC_CATEGORY=>translateFN('Per categorie EUROVOC'),
                    HOLIS_SEARCH_TEXT=>translateFN('Nel testo e titolo')
                );

                $searchTypeSel = FormControl::create(FormControl::SELECT,'searchType',translateFN('Tipo di ricerca'));
                if (isset($data['searchType'])) {
			$searchType = $data['searchType']; 
		} else {
			$searchType = reset(array_keys($searchTypeSelAr));				
                }
                $searchTypeSel->withData($searchTypeSelAr,$searchType);
                
		$this->addControl($searchTypeSel);
		$this->addControl($searchText);
//		$fieldSetFilter = array ($searchTypeSel, $searchText);
//                $this->addFieldset('','set_filtro_text')->withData($fieldSetFilter);
                
		
		if (MODULES_LEX && isset($data['typologiesArr']) && is_array($data['typologiesArr']) && count($data['typologiesArr'])>0) {
			
			require_once MODULES_LEX_PATH. '/include/management/sourceTypologyManagement.inc.php';
			
			$typologiesArr = array('null'=>translateFN('Tutte')) + $data['typologiesArr'];
			
			$sel_tipologia = FormControl::create(FormControl::SELECT, 'tipologia', translateFN('tipologia'));			
			// $sel_tipologia->setAttribute('class', 'dontuniform');			
			if (isset($data['tipologia']) && strlen($data['tipologia'])>0) {
				$selTypology = $data['tipologia']; 
			} else {
				$selTypology = reset(array_keys($typologiesArr));				
			}						
			$sel_tipologia->withData($typologiesArr,$selTypology);
			
			$categoriesArr = sourceTypologyManagement::getTypologyChildren($selTypology);
			// write 'all' instead of 'none'
			if (array_key_exists('null', $categoriesArr)) $categoriesArr['null'] = translateFN('Tutte');
			
			$sel_categoria = FormControl::create(FormControl::SELECT, 'categoria', translateFN('categoria'));
			// $sel_categoria->setAttribute('class', 'dontuniform');
			if (isset($data['categoria']) && strlen($data['categoria'])>0) {
				$selCategory = $data['categoria'];
			} else {
				$selCategory = reset(array_keys($categoriesArr));
			}
			$sel_categoria->withData($categoriesArr,$selCategory);

			$classesArr = sourceTypologyManagement::getCategoryChildren($selTypology, $selCategory);
			// write 'all' intead of 'none'
			if (array_key_exists('null', $classesArr)) $classesArr['null'] = translateFN('Tutte');
			
			$sel_classe = FormControl::create(FormControl::SELECT, 'classe', translateFN('classe(fonte)'));
			// $sel_classe->setAttribute('class', 'dontuniform');
			if (isset($data['classe']) && strlen($data['classe'])>0) {
				$selClass = $data['classe'];
			} else {
				$selClass = reset(array_keys($classesArr));
			}
			$sel_classe->withData($classesArr,$selClass);
			
			$fieldSet = array ($sel_tipologia, $sel_categoria, $sel_classe);
			
			if (!$forceAbrogated) {
				$abrogatoArr = array ('-1'=>translateFN('Tutti'), '0'=>translateFN('No'), '1'=>translateFN('Sì'));
				$sel_abrogato = FormControl::create(FormControl::SELECT, 'abrogato', translateFN('Abrogato'));
				if (isset($data['abrogato']) && intval($data['abrogato'])>-1) {
					$selAbrogato = $data['abrogato'];
				} else {
					$selAbrogato = -1;
				}
				$sel_abrogato->withData($abrogatoArr, $selAbrogato);
				array_push($fieldSet, $sel_abrogato);		
			}
			
			
			$this->addFieldset('','set_tipologia')->withData($fieldSet);
		}
	}
}
