<?php
class ADAEvent
{
  private static $tolerance = 1800;
	
  public static function generateEventMessageAction($event_type, $id_course, $id_course_instance) {
    $text = '<event_data>'
  		  . "<event_type>$event_type</event_type>"
  		  . "<id_course>$id_course</id_course>"
  		  . "<id_course_instance>$id_course_instance</id_course_instance>"
          . '</event_data>';

    return $text;
  }

  public static function parseMessageText($message_ha = array()) {
    if ($message_ha['flags'] & ADA_EVENT_PROPOSED)
    {
    	$messageDIV = CDOMElement::create('div','id:readeventdiv');
    	
    	$datesOL = self::generateProposalHTMLList( ADAEventProposal::extractDateTimesFromEventProposalText($message_ha['testo']) );
    	$notesDIV = self::genreateNotesDIV( ADAEventProposal::extractNotesFromEventProposalText($message_ha['testo']) ); 
    	
    	if (!is_null($datesOL))  $messageDIV->addChild($datesOL);
    	if (!is_null($notesDIV)) $messageDIV->addChild($notesDIV);
    	
    	$clean_message = $messageDIV->getHtml();
    }
    else
    {
    	$message_text = $message_ha['testo'];
    	$action = self::extractActionFromEventMessage($message_ha);
    	$clean_message = self::cleanMessageText($message_text)
    				   . self::appendAction($action);
    	/**
    	 * giorgio 20/set/2013
    	 *
    	 * if action is empty and event has not expired, a countdown is needed
    	 */
		$time = time() - self::$tolerance;
		
    	if (empty($action) && $message_ha['data_ora']>=$time) $clean_message .= self::generateCountDownCode($message_ha);
    	else if ($message_ha['data_ora']<$time) $clean_message .= self::generateExpiredAppointmentMessage();
    	
    }
    return $clean_message;
  }
  
  private static function generateExpiredAppointmentMessage()
  {
  	$expiredDIV = CDOMElement::create('div','id:expiredAppointmentMessage');
  		$expiredSPAN = CDOMElement::create('span','class:expiredAppointment');
  		$expiredSPAN->addChild(new CText(translateFN('Attenzione: l\'appuntamento è scaduto!')));  	
  	$expiredDIV->addChild($expiredSPAN);
  	
  	return CDOMElement::create ( 'div', 'class:clearfix' )->getHtml() . $expiredDIV->getHtml();
  }
  
  /**
   * giorgio 20/set/2013
   * 
   * generate a div for the notes if the event is a proposal
   */
  private static function genreateNotesDIV($notes) {
		if ($notes != '') {
			$notesDIV = CDOMElement::create ( 'div', 'class:readeventnotes' );
			$notesLbl = CDOMElement::create ( 'span', 'class:notesLbl' );
			$notesLbl->addChild ( new CText ( translateFN ( 'Note' ) . ":" ) );
			$notesTxt = CDOMElement::create ( 'span', 'class:notesTxt' );
			$notesTxt->addChild ( new CText ( nl2br ( $notes ) ) );
			
			$notesDIV->addChild ( $notesLbl );
			$notesDIV->addChild ( CDOMElement::create ( 'div', 'class:clearfix' ) );
			$notesDIV->addChild ( $notesTxt );
			
			return $notesDIV;
		} else
			return null;
	}
	
	/**
	 * giorgio 20/set/2013
	 * 
	 * generate an ol with date and times of proposals,
	 * gets called if the event is a proposal
	 */
	private static function generateProposalHTMLList($dateArray) {
		if (! empty ( $dateArray )) {
			$retOL = CDOMElement::create ( 'ol', 'class:readeventOL' );
			foreach ( $dateArray as $dateelement ) {
				$addLI = CDOMElement::create ( 'li', 'class:readeventLI' );
				$addLI->addChild ( new CText ( translateFN ( "Proposta il" ) . " " . $dateelement ['date'] . " " . translateFN ( "alle ore" ) . " " . $dateelement ['time'] ) );
				$retOL->addChild ( $addLI );
				$addLI = null;
			}
			return $retOL;
		} else
			return null;
	}
  
  /**
   * giorgio 20/set/2013
   * 
   * added static method for jquery countdown code generation
   * see read_event.js for javascript implementation
   * 
   */
  private static function generateCountDownCode($message_ha = array())
  {
		// if message is not confirmed, do not generate the countdown
		// this is already checked in parseMessageText, but let's double check
		// just in case this is being called from somewherew else
		if ($message_ha ['flags'] & ADA_EVENT_CONFIRMED) {
			$wrapperDIV = CDOMElement::create ( 'div', 'id:countdownWrapper' );
			// text above the countdown
			$aboveTextSPAN = CDOMElement::create ( 'span', 'class:countdownMessage above' );
			$aboveTextSPAN->addChild ( new CText ( translateFN ( 'Tra quanto c\'è il mio appuntamento?' ) ) );
			// text below the countdown
			$belowTextSPAN = CDOMElement::create ( 'span', 'class:countdownMessage below' );
			$belowTextSPAN->addChild ( new CText ( translateFN ( 'Qui troverai il link per l\'appuntamento allo scadere del conto alla  rovescia' ) ) );
			
			$countdownDIV = CDOMElement::create ( 'div', 'id:appointmentCountdown' );
			// read from js to set until param for the countdown
			$untilSPAN = CDOMElement::create ( 'span', 'id:countdownUntil' );
			$untilSPAN->setAttribute ( 'style', 'display:none;' );
			$untilSPAN->addChild ( new CText ( $message_ha ['data_ora'] ) );
			// add items to the div
			$countdownDIV->addChild ( $untilSPAN );
			// adds links to the div, will be shown when countdown expires
			$countdownDIV->addChild ( new CText ( self::appendAction ( self::extractActionFromEventMessage ( $message_ha, true ) ) ) );
			
			$wrapperDIV->addChild ( $aboveTextSPAN );
			$wrapperDIV->addChild ( $countdownDIV );
			$wrapperDIV->addChild ( $belowTextSPAN );
			
			return CDOMElement::create ( 'div', 'class:clearfix' )->getHtml() . $wrapperDIV->getHtml ();
		} else
			return '';
  }

  /**
   * giorgio 20/set/2013
   *
   * added force parameter to force the generation of the
   * link that's needed after the countdown has expired
   */
  private static function extractActionFromEventMessage($message_ha = array(), $force=false) {

    if(!$force && !self::createTheEnterLink($message_ha)) {
      return '';
    }
    $message = $message_ha['testo'];
    $subject = $message_ha['titolo'];
    $event_token = ADAEventProposal::extractEventToken($subject);
    $pattern = '/<event_data>(?:\s)*<event_type>(.*)<\/event_type>(?:\s)*<id_course>(.*)<\/id_course>(?:\s)*<id_course_instance>(.*)<\/id_course_instance>(?:\s)*<\/event_data>/';
    $matches = array();

    if(preg_match($pattern, $message, $matches) > 0) {
	if ($event_token != '') {
	    $actionToReturn = "performEnterEventSteps({$matches[1]},{$matches[2]},{$matches[3]},'$event_token');";
	} else {
	    $actionToReturn = "performEnterEventSteps({$matches[1]},{$matches[2]},{$matches[3]});";
	}
      return $actionToReturn;
    }
    return '';
  }

  private static function createTheEnterLink($message_ha = array()) {
    $event_timestamp   = $message_ha['data_ora'];
    $current_timestamp = time();
    $round = self::$tolerance;

    if($current_timestamp > $event_timestamp
    && $current_timestamp < $event_timestamp + $round) {
      return true;
    }
    return false;
  }

  private static function cleanMessageText($message) {
    $pattern = '/<event_data>(?:.*)<\/event_data>/';
    $message_text = nl2br(preg_replace($pattern, '', $message));
/*
    $message_rows = explode(chr(13),  rtrim($message_text));
    $clean_text = '';
    foreach($message_rows as $row) {
      $clean_text .= '<br />';
    }
    return $clean_text;
*/
    return $message_text;
  }


  private static function appendAction($action) {
    if(empty($action)) {
      return '';
    }
    $div = CDOMElement::create('div','id:enter_appointment');
    $link = CDOMElement::create('a');
    $link->setAttribute('href', '#');
    $link->setAttribute('onclick', $action);
    $link->addChild(new CText(translateFN('Enter the appointment')));
    $div->addChild($link);
    return $div->getHtml();
  }
}