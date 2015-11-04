<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';

require_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/FormElementCreator.inc.php';

class TutorModuleHtmlLib
{

  static public function getServiceStatusForm(ADALoggableUser $tutoredUserObj, $service_infoAr = array()) {
    $form = CDOMElement::create('form','id:user_service_detai, name: user_service_detai, action:user_service_detail.php, method:post');
      
    
   /*
     * Hidden user data
     */
    $user_fullname = $tutoredUserObj->nome . ' ' . $tutoredUserObj->cognome;
    $user_country = $tutoredUserObj->getCountry();
    $user_birthdate = $tutoredUserObj->getBirthDate();
    $user_birthcity = $tutoredUserObj->getBirthCity();
    $user_birthprovince = $tutoredUserObj->getBirthProvince();    
    $user_gender = $tutoredUserObj->getGender();
    $user_foreign_culture = 'FOREIGN CULTURE';

    if(($id = DataValidator::is_uinteger($form_dataAr['id'])) !== FALSE) {
      $hidden_id_eguidance_session  = CDOMElement::create('hidden','id:id_eguidance_session, name:id_eguidance_session');
      $hidden_id_eguidance_session->setAttribute('value', $id);
      $form->addChild($hidden_id_eguidance_session);
    }

    $hidden_id_utente  = CDOMElement::create('hidden','id:id_utente, name:id_utente');
    $hidden_id_utente->setAttribute('value', $tutoredUserObj->getId());

    $hidden_id_istanza_corso = CDOMElement::create('hidden','id:id_istanza_corso, name:id_istanza_corso');
    $hidden_id_istanza_corso->setAttribute('value', $service_infoAr['id_istanza_corso']);

    $hidden_event_token = CDOMElement::create('hidden','id:event_token, name:event_token');
    $hidden_event_token->setAttribute('value', $service_infoAr['event_token']);

    $hidden_user_fullname = CDOMElement::create('hidden', 'id:user_fullname, name: user_fullname');
    $hidden_user_fullname->setAttribute('value', $user_fullname);

    $hidden_previous_instance_status = CDOMElement::create('hidden', 'id:previous_instance_status, name: previous_instance_status');
    $hidden_previous_instance_status->setAttribute('value', $service_infoAr['instance_status_previous']);
   
    $hidden_user_country = CDOMElement::create('hidden', 'id:user_country, name:user_country');
    $hidden_user_country->setAttribute('value', $user_country);
    $hidden_service_duration = CDOMElement::create('hidden','id:service_duration, name:service_duration');
    $hidden_service_duration->setAttribute('value', 10);
    $hidden_user_birthdate = CDOMElement::create('hidden', 'id:ud_1, name:ud_1');
    $hidden_user_birthdate->setAttribute('value', $user_birthdate);
    $hidden_user_gender = CDOMElement::create('hidden', 'id:ud_2, name:ud_2');
    $hidden_user_gender->setAttribute('value', $user_gender);
    $hidden_user_foreign_culture = CDOMElement::create('hidden', 'id:ud_3, name:ud_3');
    $hidden_user_foreign_culture->setAttribute('value', $user_foreign_culture);
    $hidden_user_birthcity = CDOMElement::create('hidden', 'id:ud_4, name:ud_4');
    $hidden_user_birthcity->setAttribute('value', $user_birthcity);
    $hidden_user_birthprovince = CDOMElement::create('hidden', 'id:ud_5, name:ud_5');
    $hidden_user_birthprovince->setAttribute('value', $user_birthprovince);

    $form->addChild($hidden_id_utente);
    $form->addChild($hidden_id_istanza_corso);
    $form->addChild($hidden_event_token);
    $form->addChild($hidden_user_fullname);
    $form->addChild($hidden_user_country);
    $form->addChild($hidden_service_duration);
    $form->addChild($hidden_user_birthdate);
    $form->addChild($hidden_user_birthcity);
    $form->addChild($hidden_user_birthprovince);
    $form->addChild($hidden_user_gender);
    $form->addChild($hidden_user_foreign_culture);
    $form->addChild($hidden_previous_instance_status);
    
    $toe_thead = '';
    $instance_status = $service_infoAr['instance_status_value'];
    $avalaibleStatusAr = array($status_opened_label,$status_closed_label); 
    $more_attributes['onchange'] = 'saveStatus(this)';
    $toe_tbody = array(
      array(BaseHtmlLib::selectElement2('id:status_service, name:status_service',$service_infoAr['avalaible_status'],$instance_status,$more_attributes))
    );
    $toe_table = BaseHtmlLib::tableElement('', $toe_thead, $toe_tbody);
    $form->addChild($toe_table);
    return $form;
  }   
    
  /*
   * methods used to display forms and data for the eguidance session
   */
  // MARK: methods used to display forms and data for the eguidance session

  static public function getServiceDataTable($service_dataAr) {
      
      $avalaibleServiceTypeAr = array(translateFN('Help per studenti'),translateFN('Area comune'), translateFN('Area comune studenti'));
   // S.nome, S.descrizione, S.livello, S.durata_servizio, S.min_incontri, S.max_incontri, S.durata_max_incontro
    $thead = array(translateFN('Service data'),'');
    $tbody = array(
      array(translateFN('Name'), $service_dataAr[1]),
      array(translateFN('Description'), $service_dataAr[2]),
      array(translateFN('Level'), $avalaibleServiceTypeAr[$service_dataAr[3]])
/*        
      array(translateFN('Duration'), $service_dataAr[4]),
      array(translateFN('Min incontri'), $service_dataAr[5]),
      array(translateFN('Max incontri')       , $service_dataAr[6]),
      array(translateFN('Durata max incontro'), $service_dataAr[7])
 * 
 */
    );
    return BaseHtmlLib::tableElement('', $thead, $tbody);
  }

  /*
   * methods used to display forms and data for the eguidance session
   */
  // MARK: methods used to display forms and data for the eguidance session

static public function getServiceDataTableForTutor($service_dataAr) {
      
    $thead = array(translateFN('Service data'),'');
    $tbody = array(
	array(translateFN('Name'), $service_dataAr[1]),
	array(translateFN('Description'), $service_dataAr[2]),
	array(translateFN('Level'), $service_dataAr['level_name']),
	array(translateFN('status'), $service_dataAr['status'])
    );
    return BaseHtmlLib::tableElement('', $thead, $tbody);
  }
  
  static public function getSubscribedUsersList($user_dataAr, $id_course, $id_course_instance) {

      $form = CDOMElement::create('form','id:pe_subscribed, method:post, action:course_instance_subscribe.php');

      $thead = array(
          translateFN('studente'),
          translateFN('iscritto'),
          translateFN('sospeso'),
          translateFN('in visita'),
          translateFN('cancellato'),
          translateFN('data iscrizione')
      );
      $tbody = array();
      foreach($user_dataAr as $user) {
          $user_id = $user['id_utente'];
          
          $subscribed = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_SUBSCRIBED);
          if($user['status'] == ADA_STATUS_SUBSCRIBED) {
              $subscribed->setAttribute('checked', 'true');
          }
          $suspended = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_PRESUBSCRIBED);
          if($user['status'] == ADA_STATUS_PRESUBSCRIBED) {
              $suspended->setAttribute('checked', 'true');
          }
          $visiting = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_VISITOR);
          if($user['status'] == ADA_STATUS_VISITOR) {
              $visiting->setAttribute('checked', 'true');
          }
          $removed = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_REMOVED);
          if($user['status'] == ADA_STATUS_REMOVED) {
              $removed->setAttribute('checked', 'true');
          }


          $tbody[] = array(
              $user['nome'] . ' ' . $user['cognome'],
              $subscribed,
              $suspended,
              $visiting,
              $removed,
              ''
          );
      }

      $table = BaseHtmlLib::tableElement('', $thead, $tbody);

      $form->addChild($table);
      $form->addChild(CDOMElement::create('hidden','name: id_course, value:' . $id_course));
      $form->addChild(CDOMElement::create('hidden','name: id_course_instance, value:' . $id_course_instance));
      $submit = CDOMElement::create('submit','id:subscribed, name:subscribed');
      $form->addChild($submit);
      return $form;
  }

  static public function getPresubscribedUsersList($user_dataAr, $id_course, $id_course_instance) {
    $form = CDOMElement::create('form','id:pe_unsubscribed, method:post, action:course_instance_presubscribe.php');

      $thead = array(
          translateFN('studente'),
          translateFN('iscrivi'),
          translateFN('rimuovi richiesta'),
          translateFN('data richiesta')
      );
      $tbody = array();
      foreach($user_dataAr as $user) {
          $user_id = $user['id_utente'];

          $subscribe = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_SUBSCRIBED);
          $subscribe->setAttribute('checked', 'true');          
          $remove = CDOMElement::create('radio',"name:student[$user_id] value:".ADA_STATUS_REMOVED);
          $tbody[] = array(
              $user['nome'] . ' ' . $user['cognome'],
              $subscribe,
              $remove,
              ''
          );
      }

      $table = BaseHtmlLib::tableElement('', $thead, $tbody);

      $form->addChild($table);
      $form->addChild(CDOMElement::create('hidden','name: id_course, value:' . $id_course));
      $form->addChild(CDOMElement::create('hidden','name: id_course_instance, value:' . $id_course_instance));
      $submit = CDOMElement::create('submit','id:unsubscribed, name:unsubscribed');

      $form->addChild($submit);
      return $form;
  }

  static public function getClassroomForm($students_Ar, $presubscribed_Ar, $id_course, $id_course_instance) {
      $div = CDOMElement::create('div');

      $ol = CDOMElement::create('ol','class:pager');
      $subscribed = CDOMElement::create('li','id:subscribed');
      $subscribed->addChild(new CText(translateFN('Classe')));
      $subscribed->setAttribute('onclick',"PAGER.showPage('subscribed')");
      $unsubscribed = CDOMElement::create('li','id:unsubscribed');
      $unsubscribed->addChild(new CText(translateFN('Preiscrizioni')));
      $unsubscribed->setAttribute('onclick',"PAGER.showPage('unsubscribed')");
      $ol->addChild($subscribed);
      $ol->addChild($unsubscribed);

      $div->addChild($ol);
      $div->addChild(self::getSubscribedUsersList($students_Ar, $id_course, $id_course_instance));
      $div->addChild(self::getPresubscribedUsersList($presubscribed_Ar, $id_course, $id_course_instance));

      return $div;
  }

  static private function getUserSubscriptionStatusText($status) {
      switch($status) {
          case ADA_STATUS_PRESUBSCRIBED:
              return translateFN('preiscritto');
          case ADA_STATUS_REGISTERED:
              return translateFN('registrato');
          case ADA_STATUS_REMOVED:
              return translateFN('cancellato');
          case ADA_STATUS_SUBSCRIBED:
              return translateFN('iscritto');
          case ADA_STATUS_VISITOR:
              return translateFN('in visita');
          default:
              return '';
      }
  }
  static public function getEguidanceSessionUserDataTable(ADALoggableUser $tutoredUserObj) {

    $user_serial_number = $tutoredUserObj->getSerialNumber();
    if(is_null($user_serial_number)) {
      $user_serial_number = translateFN("L'utente non ha fornito la matricola");
    }

    $thead = array(translateFN("Dati utente"),'');
    $tbody = array(
      array(translateFN("Matricola"), $user_serial_number),
      array(translateFN("Nome e cognome dell'utente"), $tutoredUserObj->getFullName()),
      array(translateFN("Nazionalità dell'utente")   , $tutoredUserObj->getCountry())
    );
    return BaseHtmlLib::tableElement('', $thead, $tbody);
  }

  static public function displayEguidanceSessionData(ADALoggableUser $tutoredUserObj, $service_infoAr=array(), $eguidance_session_dataAr=array()) {

    $div = CDOMElement::create('div','id:eguidance_data');

    $thead_service_info = array(translateFN('Informazioni sul servizio'), '');
    $field = 'sl_'.$eguidance_session_dataAr['tipo_eguidance'];
    $tbody_service_info = array(
      array(translateFN('Servizio'), $service_infoAr[1]),
      array(translateFN('Livello') , $service_infoAr[3]),
      array(EguidanceSession::textLabelForField('toe_title'), EguidanceSession::textLabelForField($field))
    );
//    print_r($tbody_service_info);
    $div->addChild(BaseHtmlLib::tableElement('', $thead_service_info, $tbody_service_info));

    $div->addChild(self::getEguidanceSessionUserDataTable($tutoredUserObj));

    $pointsString = translateFN('Punteggio');

//    $div->addChild(new CText(EguidanceSession::textLabelForField('area_pc')));

    $thead_ud = array(EguidanceSession::textLabelForField('ud_title'), $pointsString);
    $tbody_ud = array(
      array(EguidanceSession::textLabelForField('ud_1') , EguidanceSession::textForScore($eguidance_session_dataAr['ud_1'])),
      array(EguidanceSession::textLabelForField('ud_2') , EguidanceSession::textForScore($eguidance_session_dataAr['ud_2'])),
      array(EguidanceSession::textLabelForField('ud_3') , EguidanceSession::textForScore($eguidance_session_dataAr['ud_3'])),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_ud, $tbody_ud));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('ud_comments'), $eguidance_session_dataAr['ud_comments']));

    $thead_pc = array(EguidanceSession::textLabelForField('pc_title'),$pointsString);
    $tbody_pc = array(
      array(EguidanceSession::textLabelForField('pc_1') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_1'])),
      array(EguidanceSession::textLabelForField('pc_2') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_2'])),
      array(EguidanceSession::textLabelForField('pc_3') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_3'])),
      array(EguidanceSession::textLabelForField('pc_4') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_4'])),
      array(EguidanceSession::textLabelForField('pc_5') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_5'])),
      array(EguidanceSession::textLabelForField('pc_6') , EguidanceSession::textForScore($eguidance_session_dataAr['pc_6'])),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_pc, $tbody_pc));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('pc_comments'), $eguidance_session_dataAr['pc_comments']));

    $div->addChild(new CText(EguidanceSession::textLabelForField('area_pp')));

    $thead_ba = array(EguidanceSession::textLabelForField('ba_title'),$pointsString);
    $tbody_ba = array(
      array(EguidanceSession::textLabelForField('ba_1') , EguidanceSession::textForScore($eguidance_session_dataAr['ba_1'])),
      array(EguidanceSession::textLabelForField('ba_2') , EguidanceSession::textForScore($eguidance_session_dataAr['ba_2'])),
      array(EguidanceSession::textLabelForField('ba_3') , EguidanceSession::textForScore($eguidance_session_dataAr['ba_3'])),
      array(EguidanceSession::textLabelForField('ba_4') , EguidanceSession::textForScore($eguidance_session_dataAr['ba_4'])),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_ba, $tbody_ba));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('ba_comments'), $eguidance_session_dataAr['ba_comments']));

    $thead_t = array(EguidanceSession::textLabelForField('t_title'),$pointsString);
    $tbody_t = array(
      array(EguidanceSession::textLabelForField('t_1') , EguidanceSession::textForScore($eguidance_session_dataAr['t_1'])),
      array(EguidanceSession::textLabelForField('t_2') , EguidanceSession::textForScore($eguidance_session_dataAr['t_2'])),
      array(EguidanceSession::textLabelForField('t_3') , EguidanceSession::textForScore($eguidance_session_dataAr['t_3'])),
      array(EguidanceSession::textLabelForField('t_4') , EguidanceSession::textForScore($eguidance_session_dataAr['t_4'])),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_t, $tbody_t));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('t_comments'), $eguidance_session_dataAr['t_comments']));

    $thead_pe = array(EguidanceSession::textLabelForField('pe_title'),$pointsString);
    $tbody_pe = array(
      array(EguidanceSession::textLabelForField('pe_1') , EguidanceSession::textForScore($eguidance_session_dataAr['pe_1'])),
      array(EguidanceSession::textLabelForField('pe_2') , EguidanceSession::textForScore($eguidance_session_dataAr['pe_2'])),
      array(EguidanceSession::textLabelForField('pe_3') , EguidanceSession::textForScore($eguidance_session_dataAr['pe_3'])),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_pe, $tbody_pe));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('pe_comments'), $eguidance_session_dataAr['pe_comments']));


    $thead_ci = array(EguidanceSession::textLabelForField('ci_title'),$pointsString);
    $tbody_ci = array(
      array(EguidanceSession::textLabelForField('ci_1') , EguidanceSession::textForScore($eguidance_session_dataAr['ci_1'])),
      array(EguidanceSession::textLabelForField('ci_2') , EguidanceSession::textForScore($eguidance_session_dataAr['ci_2'])),
      array(EguidanceSession::textLabelForField('ci_3') , EguidanceSession::textForScore($eguidance_session_dataAr['ci_3'])),
      array(EguidanceSession::textLabelForField('ci_4') , EguidanceSession::textForScore($eguidance_session_dataAr['ci_4'])),
    //  array(EguidanceSession::textLabelForField('ci_comments') , $eguidance_session_dataAr['ci_comments']),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_ci, $tbody_ci));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('ci_comments'), $eguidance_session_dataAr['ci_comments']));

    $thead_m = array(EguidanceSession::textLabelForField('m_title'),$pointsString);
    $tbody_m = array(
      array(EguidanceSession::textLabelForField('m_1') , EguidanceSession::textForScore($eguidance_session_dataAr['m_1'])),
      array(EguidanceSession::textLabelForField('m_2') , EguidanceSession::textForScore($eguidance_session_dataAr['m_2'])),
    //  array(EguidanceSession::textLabelForField('m_comments') , $eguidance_session_dataAr['m_comments']),
    );
    $div->addChild(BaseHtmlLib::tableElement('', $thead_m, $tbody_m));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('m_comments'), $eguidance_session_dataAr['m_comments']));

    $div->addChild(self::displayTutorCommentsForArea(EguidanceSession::textLabelForField('other_comments'), $eguidance_session_dataAr['other_comments']));

    return $div;
  }

  static private function displayTutorCommentsForArea($label, $text) {

    $div_comments = CDOMElement::create('div');
    $div_comments_title = CDOMElement::create('div','class:textarea_title');
    $div_comments_title->addChild(new CText($label));
    $div_comments_text = CDOMElement::create('div', 'class:textarea_container');
    $div_comments_text->addChild(new CText($text));
    $div_comments->addChild($div_comments_title);
    $div_comments->addChild($div_comments_text);

    return $div_comments;
  }

  static private function displayTextAreaForTutorComments($name, $label, $form_dataAr = array(), $use_existing_data = FALSE) {

    $textarea = CDOMElement::create('textarea',"id:$name, name:$name");

    if($use_existing_data && is_array($form_dataAr) && isset($form_dataAr[$name])) {
      $textarea->addChild(new CText($form_dataAr[$name]));
    }
    else {
      $textarea->addChild(new CText(translateFN('Inserire i vostri commenti')));
    }

    $div = CDOMElement::create('div');
    $div_textarea_title = CDOMElement::create('div','class:textarea_title');
    $div_textarea_title->addChild(new CText($label));
    $div_textarea = CDOMElement::create('div', 'class:textarea_container');
    $div_textarea->addChild($textarea);
    $div->addChild($div_textarea_title);
    $div->addChild($div_textarea);

    return $div;

  }

  static public function getEditEguidanceDataForm(ADALoggableUser $tutoredUserObj, $service_infoAr = array(), $form_dataAr = array()) {
    return self::getEguidanceTutorForm($tutoredUserObj,$service_infoAr, $form_dataAr, TRUE);
  }


  static public function getEguidanceTutorForm(ADALoggableUser $tutoredUserObj, $service_infoAr = array(), $form_dataAr=array(), $fill_textareas=FALSE, $readOnly = false) {
    $form = CDOMElement::create('form','id:eguidance_tutor_form, name: eguidance_tutor_form, action:eguidance_tutor_form.php, method:post');

/*    
    $area_personal_conditions = CDOMElement::create('div');
    $area_personal_conditions->addChild(new CText(EguidanceSession::textLabelForField('area_pc')));
    $form->addChild($area_personal_conditions);
 * 
 */
    /*
     * Serial Number
     */
    $user_serial_number = $tutoredUserObj->getSerialNumber();
    
    if(!is_null($user_serial_number)) {
      $hidden_fc = CDOMElement::create('hidden','id:user_fc, name:user_fc');
      $hidden_fc->setAttribute('value', $user_serial_number);
      $form->addChild($hidden_fc);
      $ufc = $user_serial_number;
    }
    else {
      $ufc = translateFN("L'utente non ha fornito la matricola");
    }
    if(isset($form_dataAr['is_popup'])) {
      $hidden_popup = CDOMElement::create('hidden','id:is_popup, name:is_popup');
      $hidden_popup->setAttribute('value', '1');
      $form->addChild($hidden_popup);
    }
    /*
     * Hidden user data
     */
    $user_fullname = $tutoredUserObj->nome . ' ' . $tutoredUserObj->cognome;
    $user_country = $tutoredUserObj->getCountry();
    $user_birthdate = $tutoredUserObj->getBirthDate();
    $user_birthcity = $tutoredUserObj->getBirthCity();
    $user_birthprovince = $tutoredUserObj->getBirthProvince();    
    $user_gender = $tutoredUserObj->getGender();
    $user_foreign_culture = 'FOREIGN CULTURE';

    if(($id = DataValidator::is_uinteger($form_dataAr['id'])) !== FALSE) {
      $hidden_id_eguidance_session  = CDOMElement::create('hidden','id:id_eguidance_session, name:id_eguidance_session');
      $hidden_id_eguidance_session->setAttribute('value', $id);
      $form->addChild($hidden_id_eguidance_session);
    }

    $appointmentDate = $form_dataAr['data_ora'];
    $hiddenAppointmentDate  = CDOMElement::create('hidden','id:data_ora, name:data_ora');
    $hiddenAppointmentDate->setAttribute('value', $appointmentDate);
    
    $hidden_id_utente  = CDOMElement::create('hidden','id:id_utente, name:id_utente');
    $hidden_id_utente->setAttribute('value', $tutoredUserObj->getId());

    $hidden_id_istanza_corso = CDOMElement::create('hidden','id:id_istanza_corso, name:id_istanza_corso');
    $hidden_id_istanza_corso->setAttribute('value', $service_infoAr['id_istanza_corso']);

    $hidden_event_token = CDOMElement::create('hidden','id:event_token, name:event_token');
    $hidden_event_token->setAttribute('value', $service_infoAr['event_token']);

    $hidden_user_fullname = CDOMElement::create('hidden', 'id:user_fullname, name: user_fullname');
    $hidden_user_fullname->setAttribute('value', $user_fullname);

    $hidden_previous_instance_status = CDOMElement::create('hidden', 'id:previous_instance_status, name: previous_instance_status');
    $hidden_previous_instance_status->setAttribute('value', $service_infoAr['instance_status_previous']);
   
    $hidden_user_country = CDOMElement::create('hidden', 'id:user_country, name:user_country');
    $hidden_user_country->setAttribute('value', $user_country);
    $hidden_service_duration = CDOMElement::create('hidden','id:service_duration, name:service_duration');
    $hidden_service_duration->setAttribute('value', 10);
    $hidden_user_birthdate = CDOMElement::create('hidden', 'id:ud_1, name:ud_1');
    $hidden_user_birthdate->setAttribute('value', $user_birthdate);
    $hidden_user_gender = CDOMElement::create('hidden', 'id:ud_2, name:ud_2');
    $hidden_user_gender->setAttribute('value', $user_gender);
    $hidden_user_foreign_culture = CDOMElement::create('hidden', 'id:ud_3, name:ud_3');
    $hidden_user_foreign_culture->setAttribute('value', $user_foreign_culture);
    $hidden_user_birthcity = CDOMElement::create('hidden', 'id:ud_4, name:ud_4');
    $hidden_user_birthcity->setAttribute('value', $user_birthcity);
    $hidden_user_birthprovince = CDOMElement::create('hidden', 'id:ud_5, name:ud_5');
    $hidden_user_birthprovince->setAttribute('value', $user_birthprovince);
    

    $form->addChild($hiddenAppointmentDate);
    $form->addChild($hidden_id_utente);
    $form->addChild($hidden_id_istanza_corso);
    $form->addChild($hidden_event_token);
    $form->addChild($hidden_user_fullname);
    $form->addChild($hidden_user_country);
    $form->addChild($hidden_service_duration);
    $form->addChild($hidden_user_birthdate);
    $form->addChild($hidden_user_birthcity);
    $form->addChild($hidden_user_birthprovince);
    $form->addChild($hidden_user_gender);
    $form->addChild($hidden_user_foreign_culture);
    $form->addChild($hidden_previous_instance_status);

//    $ufc_thead = array(translateFN("Dati utente"),'');
//    $ufc_tbody = array(
//      array(translateFN("Codice fiscale dell'utente"), $user_fiscal_code),
//      array(translateFN("Nome e cognome dell'utente"), $user_ns),
//      array(translateFN("Nazionalità dell'utente"), $user_country)
//    );
//    $ufc_table = BaseHtmlLib::tableElement('', $ufc_thead, $ufc_tbody);
    $ufc_table = self::getEguidanceSessionUserDataTable($tutoredUserObj);
    $form->addChild($ufc_table);

/*
 * Type of guidance (= service type)
 */
    $hidden_type_of_guidance  = CDOMElement::create('hidden','id:type_of_guidance, name:type_of_guidance');
    $hidden_type_of_guidance->setAttribute('value', $form_dataAr['tipo_eguidance']);
    $form->addChild($hidden_type_of_guidance);
    

    //FIXME: qui passo $form_dataAr['tipo_eguidance'], ma dovrei passare $form_dataAr['type_of_guidance']
    /**
     * SERVICE TYPE
     */
//    $toe_thead = array(EguidanceSession::textLabelForField('toe_title'));
//   
//    $toe_tbody = array(
////      array(BaseHtmlLib::selectElement2('id:type_of_guidance, name:type_of_guidance',$typeAr,$form_dataAr['tipo_eguidance']))
//      array($_SESSION['service_level'][$form_dataAr['tipo_eguidance']])
//    );
//    $toe_table = BaseHtmlLib::tableElement('', $toe_thead, $toe_tbody);
//    $form->addChild($toe_table);
//    
//    //FIXME: qui passo $form_dataAr['tipo_eguidance'], ma dovrei passare $form_dataAr['type_of_guidance']
    $toe_thead = array(translateFN('Service data'),'');
    $instance_status_value = $service_infoAr['instance_status_value'];
    $instance_status = $service_infoAr['instance_status'];

    /*
     * Patto formativo
     * $pattoFormativoAr read from config_main.inc.php
     */
    $pattoFormativoAr = $service_infoAr['tipo_patto_formativo'];
    $pattoFormativoOptionsAr = array();
    foreach ($pattoFormativoAr as $tipoPatto => $tipoPattoDesc) {
	$pattoFormativoOptionsAr[]=  translateFN($tipoPattoDesc);
    }
    $pattoSelected = $form_dataAr['tipo_patto_formativo'];
    $more_attributes['onchange'] = 'toggleVisiblePersonal(this)';	    
    $pattoFormativoSelect = BaseHtmlLib::selectElement2('id:tipo_patto_formativo, name:tipo_patto_formativo',$pattoFormativoOptionsAr,$pattoSelected,$more_attributes);
    
    /*
     * Patto formativo personalizzato
     * $tipoPersonalPattoAr read from config_main.inc.php
     */
    $spanPersonalPatto = CDOMElement::create('span','class:personal_patto');
    $pattoPersonalSelected = intval($form_dataAr['tipo_personalizzazione']);
    $pattoFormativoPersonalAr = $service_infoAr['tipo_patto_personal'];
    
    foreach ($pattoFormativoPersonalAr as $tipoPersonal => $tipoPersonalDesc) {
    	$aCheck = CDOMElement::create('checkbox','id:tipo_personalizzazione_'.$tipoPersonal.
    			',name:tipo_personalizzazione[],'.'value:'.$tipoPersonal.
    			($pattoPersonalSelected & $tipoPersonal ? ',checked:cheked' : ''));
    	
    	$aLabel = CDOMElement::create('label','for:tipo_personalizzazione_'.$tipoPersonal);
    	$aLabel->addChild(new CText(translateFN($tipoPersonalDesc)));
    	
    	$spanPersonalPatto->addChild($aCheck);
    	$spanPersonalPatto->addChild($aLabel);
    	$spanPersonalPatto->addChild(CDOMElement::create('div','class:clearfix'));
    }
    
    if ($pattoSelected == 0) {
	$spanPersonalPatto->setAttribute('style','display:none;');
    }
    
    $toe_tbody = array(
	array(translateFN('tipo').': '.$_SESSION['service_level'][$form_dataAr['tipo_eguidance']]),
	array(translateFN('status').': '.$instance_status),
	array(translateFN('Patto formativo').': '.$pattoFormativoSelect->getHtml().
			CDOMElement::create('div','class:clearfix')->getHtml().
			$spanPersonalPatto->getHtml())
    );

    $toe_table = BaseHtmlLib::tableElement('', $toe_thead, $toe_tbody);
    $form->addChild($toe_table);
    
    
    $scoresAr = EguidanceSession::scoresArray();

    $label = EguidanceSession::textLabelForField('ud_comments');
    $label .= ' '.translateFN('del') .' ' . Abstract_AMA_DataHandler::ts_to_date($form_dataAr['data_ora']); //, ADA_DATE_FORMAT.' - %R');
//    var_dump($form_dataAr);
    $form->addChild(self::displayTextAreaForTutorComments('ud_comments', $label, $form_dataAr, $fill_textareas));


   /*
	 * Form buttons
	 */
    $buttons = CDOMElement::create('div','id:buttons, name:buttons');
    $submit  = CDOMElement::create('submit','id:submit, name:submit');
    $submit->setAttribute('value', translateFN('Save'));
//    $reset   = CDOMElement::create('reset');
    $buttons->addChild($submit);
//    $buttons->addChild($reset);
    $form->addChild($buttons);

    return $form;
  }
//}

  static public function getEguidanceTutorShow(ADALoggableUser $tutoredUserObj, $service_infoAr = array(), $form_dataAr=array(), $fill_textareas=FALSE, $readOnly = false) {
    $div = CDOMElement::create('div','id:eguidance_tutor_form, name: eguidance_tutor_form');

    /*
     * Serial Number
     */
    $user_serial_number = $tutoredUserObj->getSerialNumber();
    
    /*
     * Hidden user data
     */
    $user_fullname = $tutoredUserObj->nome . ' ' . $tutoredUserObj->cognome;
    $user_country = $tutoredUserObj->getCountry();
    $user_birthdate = $tutoredUserObj->getBirthDate();
    $user_birthcity = $tutoredUserObj->getBirthCity();
    $user_birthprovince = $tutoredUserObj->getBirthProvince();    
    $user_gender = $tutoredUserObj->getGender();
    $user_foreign_culture = 'FOREIGN CULTURE';

    if(($id = DataValidator::is_uinteger($form_dataAr['id'])) !== FALSE) {
      $hidden_id_eguidance_session  = CDOMElement::create('hidden','id:id_eguidance_session, name:id_eguidance_session');
      $hidden_id_eguidance_session->setAttribute('value', $id);
      $div->addChild($hidden_id_eguidance_session);
    }

    $hidden_id_utente  = CDOMElement::create('hidden','id:id_utente, name:id_utente');
    $hidden_id_utente->setAttribute('value', $tutoredUserObj->getId());

    $hidden_id_istanza_corso = CDOMElement::create('hidden','id:id_istanza_corso, name:id_istanza_corso');
    $hidden_id_istanza_corso->setAttribute('value', $service_infoAr['id_istanza_corso']);

    $hidden_event_token = CDOMElement::create('hidden','id:event_token, name:event_token');
    $hidden_event_token->setAttribute('value', $service_infoAr['event_token']);


    $div->addChild($hidden_id_utente);
    $div->addChild($hidden_id_istanza_corso);
    $div->addChild($hidden_event_token);

    $ufc_table = self::getEguidanceSessionUserDataTable($tutoredUserObj);
    $div->addChild($ufc_table);
    
    $toe_thead = array(translateFN('Service data'),'');
    $instance_status_value = $service_infoAr['instance_status_value'];
    $instance_status = $service_infoAr['instance_status'];

    /*
     * Patto formativo
     * $pattoFormativoAr read from config_main.inc.php
     */
    CDOMElement::create('span','class:personal_patto');
    $pattoFormativoSpan = CDOMElement::create('span','class:patto_formativo'); 
    
    $pattoFormativoAr = $service_infoAr['tipo_patto_formativo'];
    $pattoPersonalSelectedDesc = NULL;
    $pattoSelected = $form_dataAr['tipo_patto_formativo'];
    $pattoSelectedDesc = translateFN($pattoFormativoAr[$pattoSelected]);
    $pattoFormativoSpan->addChild(new CText($pattoSelectedDesc));
    
    /*
     * Patto formativo personalizzato
     * $tipoPersonalPattoAr read from config_main.inc.php
     */
    $pattoFormativoPersonalAr = $service_infoAr['tipo_patto_personal'];
    if ($pattoSelected == MC_PATTO_FORMATIVO_PERSONALIZZATO) {
        $pattoPersonalSelected = $form_dataAr['tipo_personalizzazione'];
	$pattoPersonalSelectedDesc = ' '.translateFN('per');
	foreach ($pattoFormativoPersonalAr as $tipoPersonal => $tipoPersonalDesc) {
		if ($pattoPersonalSelected & $tipoPersonal) $pattoPersonalSelectedDesc .= ' '.translateFN($tipoPersonalDesc).',';
	}
	$pattoPersonalSelectedDesc = rtrim($pattoPersonalSelectedDesc,',');
	
	$pattoFormativoSpan->addChild(new CText($pattoPersonalSelectedDesc));
    }
	
    $toe_tbody = array(
	array(translateFN('tipo').': '.$_SESSION['service_level'][$form_dataAr['tipo_eguidance']]),
	array(translateFN('status').': '.$instance_status),
	array(translateFN('Patto formativo').': '.$pattoFormativoSpan->getHtml())
    );

    $toe_table = BaseHtmlLib::tableElement('', $toe_thead, $toe_tbody);
    $div->addChild($toe_table);
    
    $label = EguidanceSession::textLabelForField('ud_comments');
    $label .= ' '.translateFN('del') .' ' . Abstract_AMA_DataHandler::ts_to_date($form_dataAr['data_ora']); //, ADA_DATE_FORMAT.' - %R');
    
    $divUdComments = CDOMElement::create('div','id:ud_comments');
    $spanlabel = CDOMElement::create('span','id:label_ud_comments');
    
    $spanlabel->addChild(new CText($label.':'));
    $divUdComments->addChild($spanlabel);
    $divContainer = CDOMElement::create('div', 'class:ud_commnents_container');
    $divContainer->addChild(new CText(nl2br($form_dataAr['ud_comments'])));
    $divUdComments->addChild($divContainer);
    
    $div->addChild($divUdComments);


    return $div;
  }
}

class EguidanceSession
{
  private static $labels = array(
    'area_pc'     => "Andamento dell'appuntamento",

    'ud_title'    => 'Criticità dal punto di vista socio-anagrafico verso una situazionae lavorativa e/o formativa',
    'ud_1'        => 'Data di Nascita',
    'ud_2'        => 'Sesso',
    'ud_3'        => 'Cultura straniera',
  	'ud_4'		  => 'Comune o stato estero di nascita',
  	'ud_5'		  => 'Provincia di nascita',  		
    'ud_comments' => "Commenti sull'andamento dell'incontro",
      
    'sl_0'        => 'Help per studente',
    'sl_1'        => 'Colloquio informativo - utente nazionale',
    'sl_2'        => 'Colloquio informativo - utente straniero',
    'sl_3'        => 'Consulenza orientativa individuale - scolastico/formativa',
    'sl_4'        => 'Consulenza orientativa individuale - professionale',
    'sl_5'        => 'Laboratorio di ricerca attiva del lavoro',
    'sl_6'        => 'Bilancio di competenze',
    'sl_7'        => 'Tutorato e accompagnamento al lavoro',

    'toe_title'   => 'Dati intervento di orientamento a distanza',
//    'toe_title'   => 'Tipologia di intervento di orientamento a distanza',

    'pc_title'    => 'Criticità della sfera personale',
    'pc_1'        => 'Problemi fisici',
    'pc_2'        => 'Mancanza di una rete familiare',
    'pc_3'        => 'Scarsa autonomia',
    'pc_4'        => 'Scarsa cura di sé',
    'pc_5'        => 'Poca capacità di comunicare/interagire con gli altri',
    'pc_6'        => 'Storia personale problematica',
    'pc_comments' => "I vostri commenti sulle caratteristiche critiche personali dell'utente",

    'area_pp'     => "Sfera del progetto professionale e/o formativo/educativo dell'utente",

    'ba_title'    => 'Vincoli/mancanza di disponibilità',
    'ba_1'        => 'Obblighi derivanti da legami familiari/assistenza',
    'ba_2'        => 'Problemi economici urgenti/necessità immediata di lavorare',
    'ba_3'        => 'Vincoli nella gestione del tempo',
    'ba_4'        => 'Vincoli in termini di mobilità',
    'ba_comments' => "I vostri commenti sui punti critici riferiti ai vincoli/mancanza di disponibilità dell'utente",

    't_title'     => 'Criticità in ambito scolastico/formativo',
    't_1'         => 'Poca conoscenza della lingua del paese',
    't_2'         => 'Basso livello scolastico',
    't_3'         => "Scarsa conoscenza dell'inglese o di un'altra seconda lingua",
    't_4'         => 'Scarse conoscenze informatiche',
    't_comments'  => "I vostri commenti sugli aspetti critici dell'istruzione e formazione dell'utente",

    'pe_title'    => 'Criticità in ambito professionale',
    'pe_1'        => 'Difficoltà a mantenere un posto di lavoro',
    'pe_2'        => 'Lunghi periodi di inattività',
    'pe_3'        => 'Esperienze professionali non documentate',
    'pe_comments' => "I vostri commenti sulle esperienze professionali dell'utente",

    'ci_title'    => 'Criticità relative alla capacità di realizzare progetti educativi/formativi o professionali',
    'ci_1'        => 'Poca chiarezza sugli obiettivi professionali ed educativi',
    'ci_2'        => 'Poca consapevolezza dei propri limiti e risorse personali',
    'ci_3'        => 'Poca conoscenza del mercato del lavoro e delle tecniche per una ricerca attiva del lavoro (ossia CV, metodi di ricerca del lavoro, ecc.)',
    'ci_4'        => 'Eccessiva selettività nella ricerca del lavoro',
    'ci_comments' => "I vostri commenti sulle problematicità dell'utente relative alla messa a punto di un progetto scolastico/formativo e/o professionale",

    'm_title'     => 'Motivazione personale',
    'm_1'         => 'Poca "attivazione" (comportamento passivo/scetticismo)',
    'm_2'         => 'Poca disponibilità (resistenza ad accettare proposte)',
    'm_comments'  => "I vostri commenti sulle caratteristiche critiche dell'utente riferite alla sua motivazione",

    'oc_title'    => '',
    'other_comments' => 'Altri particolari commenti'
  );

  private static $scores = array(
    0 => 'Problema non rilevato',
    1 => 'Problema assente',
    2 => 'Problema presente',
    3 => 'Problema chiaramente presente'
  );

  static public function textLabelForField($field_name) {
    if(isset(self::$labels[$field_name])) {
      return translateFN(self::$labels[$field_name]);
    }

    return '';
  }

  static public function textForScore($score) {
    if(isset(self::$scores[$score])) {
      return translateFN(self::$scores[$score]);
    }

    return '';
  }

  static public function textForEguidanceType($type) {
    $key = 'sl_'.$type;
    if(isset(self::$labels[$key])) {
      return translateFN(self::$labels[$key]);
    }
    return '';
  }

  static public function scoresArray() {
    $scoresAr = array();

    foreach(self::$scores as $key => $text) {
      $scoresAr[$key] = translateFN($text);
    }

    return $scoresAr;
  }
}

?>