<?php
/**
 * File add_note.php
 *
 * tutor and user  can add notes.
 *
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */
/**
 * Base config file 
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Performs basic controls before entering this module
 */
/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('layout', 'course', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/browsing_functions.inc.php';
include_once ROOT_DIR . '/services/include/NodeEditing.inc.php';

/*
 * YOUR CODE HERE
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $idCourseTmp = explode('_', $_POST['parentNodeId']);
    $instanceId = $_POST['instanceId'];
    $id_course = $idCourseTmp[0];
      $node_data = array(
        'name' => $_POST['subject'],
        'type' => ADA_GROUP_TYPE,
        'id_node_author' => $_POST['userId'],
        'parent_id' => $_POST['parentNodeId'],
        'type' => ADA_NOTE_TYPE,
        'text' => $_POST['text'],
        'id_course'=> $id_course
      );
      $result = NodeEditing::createNode($node_data);
      if(AMA_DataHandler::isError($result)) {
          //
      }
}
      header('Location: ' . HTTP_ROOT_DIR.'/browsing/sview.php?id_node='.$result.'&id_course='.$id_course.'&id_course_instance='.$instanceId.'#'.$result);
      exit();
