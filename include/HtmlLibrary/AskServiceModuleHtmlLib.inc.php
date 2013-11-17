<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/FormElementCreator.inc.php';

class AskServiceModuleHtmlLib {
    static public function getFeedbackTextHtml($dataAr=array()) {
        
        $question = nl2br($dataAr['question']);
        $testo = translateFN("Gentile ");
        $testo.= $dataAr['name']." ".$dataAr['surname']. ',<BR />';
        $testo.=translateFN(" hai chiesto aiuto a proposito di: ");
        $testo.= $dataAr['service_name'].".".'<BR />';
        $testo .= translateFN('La tua domanda') . ':<BR /> ' . $question.'<BR />';
        $testo.=translateFN(" Riceverai un messaggio contenente le proposte di appuntamento. "). '<BR />';

        $info_div = CDOMElement::create('DIV', 'id:info_div');
        $info_div->setAttribute('class', 'info_div');
        $label_text = CDOMElement::create('span','class:info');
        $label_text->addChild(new CText($testo));
        $info_div->addChild($label_text);
        
        $homeUser = $dataAr['userHomePage'];
        $link_span = CDOMElement::create('span','class:info_link');
        $link_to_home = BaseHtmlLib::link($homeUser, translateFN('vai alla home per accedere.'));
        $link_span->addChild($link_to_home);
        $info_div->addChild($link_span);
        //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
        return $info_div;
        
    }

    static public function getFeedbackTextPlain($dataAr= array()) {
        
        $question = ($dataAr['question']);
        $testo = translateFN("Gentile ");
        $testo.= $dataAr['name']." ".$dataAr['surname']. ','. PHP_EOL;
        $testo.=translateFN("hai chiesto aiuto a proposito di: ");
        $testo.= $dataAr['service_name'].".".PHP_EOL;
        $testo .= translateFN('La tua domanda') . ':' . PHP_EOL . $question.PHP_EOL.PHP_EOL;
        $testo.= PHP_EOL.translateFN("Sarai contattato al più presto da un nostro esperto. "). PHP_EOL  ;
        $testo .= translateFN('Per accedere ai servizi di'). ': ' . PORTAL_NAME . ' ' . translateFN('Segui questo indirizzo'). ': ' . PHP_EOL;
        $testo .= $dataAr['userHomePage'];
        

        return  $testo;
        
    }
    
    static public function getToSwitcherTextHtml($dataAr=array()) {
        
        $question = nl2br($dataAr['question']);
        $link = HTTP_ROOT_DIR."/switcher/assign_practitioner.php";
        $testo = translateFN("Gentile ");
        $testo.= $dataAr['name']." ".$dataAr['surname']. ',<BR />';
        $testo.= $dataAr['asking_name']." ".$dataAr['asking_surname'] . ' ('.$dataAr['asking_username'].')' ;
        $testo.= translateFN(" ha chiesto aiuto a proposito di: ");
        $testo.= $dataAr['service_name'].".".'<BR />';
        $testo .= translateFN('Il testo della domanda è il seguente:') . ':<BR /> ' . $question.'<BR />'.'<BR />';
        /*
         * 
        $testo.= translateFN("Per assegnare un practitioner all'utente segui il link:").'<BR />';
        $testo.= $link.'<BR />';
         * 
         */
        

        $info_div = CDOMElement::create('DIV', 'id:info_div');
        $info_div->setAttribute('class', 'info_div');
        $label_text = CDOMElement::create('span','class:info');
        $label_text->addChild(new CText($testo));
        $info_div->addChild($label_text);
        
        $AssignLink = $link;
        $link_span = CDOMElement::create('span','class:info_link');
        $link_to_assign = BaseHtmlLib::link($AssignLink, translateFN("Assegna un practitioner all'utente prima possibile"));
        $link_span->addChild($link_to_assign);
        $info_div->addChild($link_span);
        //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
        return $info_div;
        
    }

    
}
?>