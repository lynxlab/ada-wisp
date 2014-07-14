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

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout','user'),
		AMA_TYPE_AUTHOR => array('layout','user'),
		AMA_TYPE_TUTOR => array('layout','user'),
		AMA_TYPE_STUDENT => array('layout','user')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_HOLISSEARCH_PATH .'/config/config.inc.php';

$retArray = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($courseID) && intval($courseID)>0 && isset($position) && intval($position)>=0 && 
    isset($searchTerms) && is_array($searchTerms) && count($searchTerms)>0) {
	
	$courseID = intval($courseID);
	
	$testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseID);
	// close the session if not needed, this is important for
	// the ajax call to not be block until the script ends and the session is closed
	session_write_close();
	
	if (!AMA_DB::isError($testerInfo) && count($testerInfo)>0 && isset($testerInfo['puntatore']) && strlen($testerInfo['puntatore'])>0) {
		if(isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
		$dh = AMAHolisSearchDataHandler::instance(MultiPort::getDSN($testerInfo['puntatore']));
		
		if (!AMA_DB::isError($dh)) {
			
			// search the following columms
			$colToSearch = array ('nome', 'titolo', 'testo');
			$match = ' MATCH ('.implode(',', $colToSearch).') AGAINST (\''.implode(' ', $searchTerms).'\' IN NATURAL LANGUAGE MODE)';
			// ask for the following columns			
			$out_fields_ar = array('nome','titolo','testo','tipo','id_utente', $match.'AS score');
			
			// build the where clause to be used
			$clause = "(";
// 			foreach ($colToSearch as $colNum=>$colName) {
// 				foreach ($searchTerms as $searchNum=>$searchTerm) {
// 					$clause .= $colName." LIKE ".$dh->quote( '%'.str_replace('_', ' ', $searchTerm).'%' );
// 					if ($searchNum!=count($searchTerms)-1 || $colNum!=count($colToSearch)-1) $clause .= ' OR ';
// 				}
// 			}

			$clause .= $match;
			
			$clause = $clause . ")";			
			$clause = $clause.' AND ((tipo <> '.ADA_PRIVATE_NOTE_TYPE.') OR (tipo ='.ADA_PRIVATE_NOTE_TYPE.' AND id_utente = '.$userObj->getId().'))';
			
			// do the search
			$resHa = $dh->find_course_nodes_list($out_fields_ar, $clause, $courseID);
			
			if (!AMA_DB::isError($resHa)) {
				if (count($resHa)>0) {
					// separate node of type ADA_PRIVATE_NOTE_TYPE from standard
					// nodes and build needed arrays to build returned html tables
					$notesAr = array();
					$nodesAr = array();
					
					if (isset($searchtext) && strlen(trim($searchtext))>0) {
						$querystring = 'querystring='.urlencode(trim($searchtext));
					} else {
						$querystring = '';
					}
					
					
					$thead_data = array(
							translateFN('Titolo'),
							translateFN('Keywords'),
							translateFN('Peso')
					);
					
					foreach ($resHa as $j=>$resultEl) {
						
						$res_id_node = $resultEl[0];
						$res_name = $resultEl[1];
						$res_course_title = $resultEl[2];
						$res_text = $resultEl[3];
						$res_type =  $resultEl[4];
						// 5 is id_utente
						$res_score =  number_format($resultEl[6],2);
						
						if( $res_type == ADA_GROUP_TYPE || $res_type == ADA_LEAF_TYPE || ADA_GROUP_WORD_TYPE || $res_type == ADA_LEAF_WORD_TYPE || $res_type == ADA_NOTE_TYPE || $res_type == ADA_PRIVATE_NOTE_TYPE) {
							
							$queryParams[] = 'id_course='.$courseID;
							
							if ($userObj->getType()==AMA_TYPE_STUDENT) {
								$instancesAr = $dh->get_course_instance_for_this_student_and_course_model($userObj->getId(),$courseID);
								/**
								 * user should not have more than one active instance
								 * anyway, just take the first one as $getAll is not passed to $dh method call above
								 * 
								 */
								if (!AMA_DB::isError($instancesAr)) {
									$queryParams[] = 'id_course_instance='.$instancesAr['istanza_id'];
								}
							} else if ($userObj->getType()==AMA_TYPE_TUTOR) {
								$instancesAr = $dh->get_tutors_assigned_course_instance($userObj->getId(), $courseID);
								/**
								 * in case of tutor, get the first returned instance?!?!
								 *
								 */
								if (!AMA_DB::isError($instancesAr)) {
									$instance = reset ($instancesAr[$userObj->getId()]);
									$queryParams[] = 'id_course_instance='.$instance['id_istanza_corso'];
								}
							}
							
							$queryParams[] = 'id_node='.$res_id_node;
							if (strlen($querystring)>0) $queryParams[] = $querystring;
							
							$viewNodeLink = CDOMElement::create('a','class:tooltip,target:_blank,href:'.HTTP_ROOT_DIR.'/browsing/view.php?'.implode('&', $queryParams));
							$viewNodeLink->setAttribute('title', translateFN('Clicca per andare al contenuto'));
							$viewNodeLink->addChild(new CText($res_name));
							
// 							$html_for_result = "<a href=\"".HTTP_ROOT_DIR."/browsing/view.php?".implode('&', $queryParams)."\" target=\"_blank\">$res_name</a>";
							// clean queryParams for next iteration
							unset($queryParams);
						}
						
						$temp_results = array($thead_data[0] => $viewNodeLink->getHtml(),
											  $thead_data[1] => $res_course_title,
											  $thead_data[2] => $res_score);
						
						if ($resultEl[4]{0}==ADA_NOTE_TYPE) { // 4 is tipo
							array_push ($notesAr,$temp_results);
						} else {
							array_push ($nodesAr,$temp_results);
						}
					}
					// at this poin $nodesAr has all non notes nodes and $notesAr has notes
					// build the html tables to be served
					
					// course info needed for the table caption+
					$courseInfo = $dh->get_course($courseID);
					if (!AMA_DB::isError($courseInfo) && count($courseInfo)>0) {
						$caption = $courseInfo['titolo'];
					} else $caption = '';
	
					$data = null;
					
					if (count($nodesAr)>0) {
						$title = CDOMElement::create('h3','class:tooltip');
						$title->setAttribute('title', translateFN('Clicca per espandere/ridurre'));
						$title->addChild (new CText( $caption.'-'.translateFN('Nodi')));
						$result_table = BaseHtmlLib::tableElement('class:nodesResultsTable', $thead_data, $nodesAr);
						$data = $title->getHtml().$result_table->getHtml();
					}
					
					if (count($notesAr)>0) {
						$result_table = BaseHtmlLib::tableElement('class:notesResultsTable', $thead_data, $nodesAr, array(), $caption.'-'.translateFN('Note'));
						if (is_null($data)) $data=$result_table->getHtml();
						else $data.=$result_table->getHtml();
					}
				} else $data = null;

				$retArray = array ('status'=>'OK', 'position'=>intval($position), 'data'=>$data);				
				
			} else {
				$retArray = array ('status'=>'ERROR', 'data'=>'DB query error');
			}// if (!AMA_DB::isError($resHa))
		} else {
			$retArray = array ('status'=>'ERROR', 'data'=>'Cannot get data handler info for pointer:'.$testerInfo['puntatore'].', courseID='.$courseID);
		} // if (!AMA_DB::isError($dh))
		
	} else {
		$retArray = array ('status'=>'ERROR', 'data'=>'Cannot get provider info for courseID='.$courseID);
	} // if (!AMA_DB::isError($testerInfo)... 	
}
echo json_encode($retArray);

