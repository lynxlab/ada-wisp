<?php
/**
 * GET EVENT PROPOSALS.
 *
 * @package		comunica
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
// ini_set('display_errors', '0'); error_reporting(E_ALL);
require_once realpath ( dirname ( __FILE__ ) ) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array (
		'layout',
		'user',
		'course',
		'course_instance'
);

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array (
		AMA_TYPE_TUTOR,
		AMA_TYPE_STUDENT
);

/**
 * Get needed objects
 */
$neededObjAr = array ();

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';


include_once ROOT_DIR . '/comunica/include/comunica_functions.inc.php';
ComunicaHelper::init($neededObjAr);

$proposedDateAndTimeAr = array ();

if (isset ( $_SERVER ['REQUEST_METHOD'] ) && $_SERVER ['REQUEST_METHOD'] == 'GET') {

	if (isset ( $_GET ['type'] ) && trim ( $_GET ['type'] ) === 'C') {
		$user_eventsAr = MultiPort::getUserAgenda ( $userObj );
		foreach ($user_eventsAr as $eventElementAr ) {
			$i=0;
			foreach ( $eventElementAr as $eventElement ) {
				$proposedDateAndTimeAr [$i] ['start'] = $eventElement[1];
				$proposedDateAndTimeAr [$i] ['title'] = ADAEventProposal::removeEventToken ( $eventElement [2] );

				$flag = $eventElement [5];
				if ($flag & ADA_VIDEOCHAT_EVENT)
					$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in videochat' );
				else if ($flag & ADA_CHAT_EVENT)
					$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in chat' );
				else if ($flag & ADA_PHONE_EVENT)
					$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento telefonico' );
				else if ($flag & ADA_IN_PLACE_EVENT)
					$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in presenza' );

				$proposedDateAndTimeAr [$i] ['recipientFullName'] = $eventElement[7]." ".$eventElement[8];
				$proposedDateAndTimeAr [$i] ['notes'] = '';

				$i++;
			}
		}
	} else {
		$user_events_proposedAr = MultiPort::getTutorEventsProposed ( $userObj );

		foreach ( $user_events_proposedAr as $eventElementAr ) {
			// there shall be an array for each provider,
			// but in this platform there's going to be one provider only(?)
			$i = 0;
			foreach ( $eventElementAr as $eventElement ) {

				$recipientFName = '';
				$recipientLName = '';

				$datesAr = ADAEventProposal::extractDateTimesFromEventProposalText ( $eventElement [9] );
				$notes = nl2br ( ADAEventProposal::extractNotesFromEventProposalText ( $eventElement [9] ) );
				$title = ADAEventProposal::removeEventToken ( $eventElement [2] );
				$flag = $eventElement [5];

				// retrieve recipient's full name
				if (isset ( $eventElement [10] )) {
					$recipientAr = $eventElement [10];
					foreach ( $recipientAr as $recipient ) {
						foreach ( $recipient as $x => $val ) {
							if ($x == 0)
								$recipientFName = $val;
							if ($x == 1)
								$recipientLName = $val;
						}
					}
				}

				for($j = 0; $j < count ( $datesAr ); $j ++) {
					list ( $dd, $mm, $yy ) = explode ( "/", $datesAr [$j] ['date'] );
					list ( $HH, $MM ) = explode ( ":", $datesAr [$j] ['time'] );

					$timestamp = mktime ( $HH, $MM, 0, $mm, $dd, $yy );

					if ($timestamp >= intval ( $_GET ['start'] ) && $timestamp <= intval ( $_GET ['end'] )) {
						$proposedDateAndTimeAr [$i] ['title'] = $title . PHP_EOL . translateFN ( 'con l\'utente' ) . ': ' . $recipientFName . ' ' . $recipientLName;
						$proposedDateAndTimeAr [$i] ['recipientFullName'] = $recipientFName . ' ' . $recipientLName;
						$proposedDateAndTimeAr [$i] ['notes'] = $notes;
						$proposedDateAndTimeAr [$i] ['start'] = $timestamp;

						if ($flag & ADA_VIDEOCHAT_EVENT)
							$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in videochat' );
						else if ($flag & ADA_CHAT_EVENT)
							$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in chat' );
						else if ($flag & ADA_PHONE_EVENT)
							$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento telefonico' );
						else if ($flag & ADA_IN_PLACE_EVENT)
							$proposedDateAndTimeAr [$i] ['type'] = translateFN ( 'Appuntamento in presenza' );

						$i ++;
					}
				}
			}
		}
	}
}

echo json_encode ( $proposedDateAndTimeAr );