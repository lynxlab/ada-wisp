<?php
/**
 * Edit user - this module provides edit user functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com> 
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 * 
 * WARNING:		THIS FILE IS INCLUDED IN /switcher/edit_user.php FOR THE SWITHCER
 * 				TO EDIT A USER PROFILE.
 * 				PAY ATTENTION TO SWITHCER ROLE WHEN EDITING THE FILE
 */
/**
 * Base config file
 */

require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout'),
    AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
$languages = Translator::getLanguagesIdAndName();

/**
 * Set the $editUserObj depending on logged user type
 */
$editUserObj = null;
$self_instruction = isset($self_instruction) ? $self_instruction : null;

switch($userObj->getType()) {
	case AMA_TYPE_STUDENT:
	case AMA_TYPE_AUTHOR:
		$editUserObj = clone $userObj;
		break;
    case AMA_TYPE_TUTOR: // UNIMC only: tutor can view/edit user profile
	case AMA_TYPE_SWITCHER:
		$userId = DataValidator::is_uinteger($_GET['id_user']);
		if ($userId !== false) {
			$editUserObj = MultiPort::findUser($userId);
		} else {
			$data = new CText(translateFN('Utente non trovato'));
		}	
		break;
}

if (!is_null($editUserObj) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserProfileForm($languages);
    $form->fillWithPostData();
    $password = trim($_POST['password']);
    $passwordcheck = trim($_POST['passwordcheck']);
    if(DataValidator::validate_password_modified($password, $passwordcheck) === FALSE) {
	    $message = translateFN('Le password digitate non corrispondono o contengono caratteri non validi.');
	    header("Location: edit_user.php?message=$message");
	    exit();
  	}
    if ($form->isValid()) {
        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
            $user_layout = $_POST['layout'];
        } else {
            $user_layout = '';
        }
        
        // set user datas
        $editUserObj->fillWithArrayData($_POST);

        // set user extra datas if any
        if ($editUserObj->hasExtra()) $editUserObj->setExtras($_POST);
        
        MultiPort::setUser($editUserObj, array(), true, ADAUser::getExtraTableName() );
        /**
         * Set the session user to the saved one if it's not
         * a switcher, that is not saving its own profile
         */
        if ($_SESSION['sess_userObj']->getType() != AMA_TYPE_SWITCHER) {
        	$_SESSION['sess_userObj'] = $editUserObj;
        }
        /* unset $_SESSION['service_level'] to reload it with the correct  user language translation */
        unset($_SESSION['service_level']);
        
        $navigationHistoryObj = $_SESSION['sess_navigation_history'];
        $location = $navigationHistoryObj->lastModule();
        header('Location: ' . $location);
        exit();
    }
} else if (!is_null($editUserObj)) {
    $allowEditProfile=false;
    /**
	 * If the user is a switcher, can edit confirmation state of student
     */
    $allowEditConfirm= ($userObj->getType()==AMA_TYPE_SWITCHER);
    $user_dataAr = $editUserObj->toArray();
    if($userObj->getType()==AMA_TYPE_AUTHOR)
    {
	    /**
	     * UNIMC only: tutor can view/edit user profile
	     * removed: || $userObj->getType()==AMA_TYPE_TUTOR
	     * from the above if condition
	     * 
	     */
        header('Location: ' .$userObj->getEditProfilePage() );
        exit();
    }
    // the standard UserProfileForm is always needed.
    // Let's create it
    $form = new UserProfileForm($languages,$allowEditProfile, $allowEditConfirm, $self.'.php');
    unset($user_dataAr['password']);
    $user_dataAr['email'] = $user_dataAr['e_mail'];
    unset($user_dataAr['e_mail']);
    $form->fillWithArrayData($user_dataAr);   
    $form->doNotUniform();
    
    if (!$editUserObj->hasExtra()) {
    	// user has no extra, let's display it
    	$data = $form->render();
    } else {
    	require_once ROOT_DIR .'/include/HtmlLibrary/UserExtraModuleHtmlLib.inc.php';
    	
    	// the extra UserExtraForm is needed as well
    	require_once ROOT_DIR . '/include/Forms/UserExtraForm.inc.php';
    	$extraForm = new UserExtraForm ($languages);
    	$extraForm->fillWithArrayData ($user_dataAr);
    	$extraForm->doNotUniform();
    	
    	array_walk ($user_dataAr, function (&$value) {
    		if(is_string($value)) $value = htmlentities ($value, ENT_QUOTES, ADA_CHARSET);
    	});
    	
    	// UNIMC Only: CorsoStudio Form
    	require_once ROOT_DIR . '/include/Forms/UserCorsoStudioForm.inc.php';
    	$corsoStudioForm = new UserCorsoStudioForm ();
    	$corsoStudioForm->fillWithArrayData ($user_dataAr);
    	$corsoStudioForm->doNotUniform();
    	// UNIMC Only: TipoIscrizione Form
    	require_once ROOT_DIR . '/include/Forms/UserTipoIscrizioneForm.inc.php';
    	$tipoIscrizioneForm = new UserTipoIscrizioneForm ();
    	$tipoIscrizioneForm->fillWithArrayData ($user_dataAr);
    	$tipoIscrizioneForm->doNotUniform();
    	// UNIMC Only: Disabilità Form
    	require_once ROOT_DIR . '/include/Forms/UserDisabilitaForm.inc.php';
    	$disabilitaForm = new UserDisabilitaForm ();
    	$disabilitaForm->fillWithArrayData ($user_dataAr);
    	$disabilitaForm->doNotUniform();
    	// UNIMC Only: Titolo studio Form
    	require_once ROOT_DIR . '/include/Forms/UserTitoloStudioForm.inc.php';
    	$titoloStudioForm = new UserTitoloStudioForm ();
    	$titoloStudioForm->fillWithArrayData ($user_dataAr);
    	$titoloStudioForm->doNotUniform();
    	// UNIMC Only: Esami is not going to be an actual form, just a
    	// loading div and a result div to be populated by an ajax call
    	$carrieraDIV = CDOMElement::create('div','id:carriera_container');    	
    	$carrieraLoad = CDOMElement::create('div','id:carriera_load');
    	$carrieraLoad->addChild(CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/js/include/jquery/ui/images/ui-anim_basic_16x16.gif'));
    	$carrieraLoadSpan = CDOMElement::create('span');
    	$carrieraLoadSpan->addChild(new CText('Caricamento in corso...'));
    	$carrieraLoad->addChild($carrieraLoadSpan);    	
    	$carrieraResult = CDOMElement::create('div','id:carriera_results');
    	$carrieraError = CDOMElement::create('div','id:carriera_error');
    	$carrieraErrorSpan = CDOMElement::create('span');
    	$carrieraErrorSpan->addChild(new CText('Errore sconosciuto'));
    	$carrieraError->addChild($carrieraErrorSpan);
    	
    	$carrieraDIV->addChild($carrieraLoad);
    	$carrieraDIV->addChild($carrieraResult);
    	$carrieraDIV->addChild($carrieraError);
    	
		$tabContents = array ();
		
		/**
		 * @author giorgio 22/nov/2013
		 * Uncomment and edit the below array to have the needed
		 * jQuery tabs to be used for user extra data and tables
		 */
		
		$tabsArray = array (
				array (translateFN ("Anagrafica"), $form, 'withExtra'=>true),
// 				array (translateFN ("Anagrafica Estesa"), $extraForm),
				array (translateFN ("Corso di Studio"), $corsoStudioForm),
				array (translateFN ("Tipo di Iscrizione"), $tipoIscrizioneForm),
				array (translateFN ("Eventuali disabilità"), $disabilitaForm),
				array (translateFN ("Titolo di studio superiore"), $titoloStudioForm),
				array (translateFN ("Carriera"), $carrieraDIV,'onclick'=>'javascript:loadCareer(\''.
						$carrieraResult->getAttribute('id').'\',\''.
						$carrieraLoad->getAttribute('id').'\',\''.
						$carrieraError->getAttribute('id').'\');')
// 				array (translateFN ("Sample Extra 1:n"), 'oneToManyDataSample'), 
		);
		
		$data = "";
		$linkedTablesInADAUser = !is_null(ADAUser::getLinkedTables()) ? ADAUser::getLinkedTables() : array();
		for($currTab = 0; $currTab < count ($tabsArray); $currTab ++) {
			
			// if is a subclass of FForm the it's a multirow element
			$doMultiRowTab = !(is_subclass_of($tabsArray[$currTab][1], 'FForm') || 
							   is_a($tabsArray[$currTab][1], 'CDiv'));
			if ($doMultiRowTab === true)
			{
				$extraTableName = $tabsArray[$currTab][1];
				$extraTableFormClass = "User" . ucfirst ($extraTableName) . "Form";
			
				/*
				 * if extraTableName is not in the linked tables array or there's
				 * no form classes for the extraTableName skip to the next iteration
				 *
				 * NOTE: there's no need to check for classes (data classes, not for ones)
				 * existance here because if they did not existed you'd get an error while loggin in.
				 */ 
				if (!in_array($extraTableName,$linkedTablesInADAUser) ||
				    !@include_once (ROOT_DIR . '/include/Forms/' . $extraTableFormClass . '.inc.php') )
					continue;

				// if the file is included, but still there's no form class defined
				// skip to the next iteration
				if (!class_exists($extraTableFormClass)) continue;
				
				// generate the form
				$form = new $extraTableFormClass ($languages);
				$form->fillWithArrayData (array (
						$extraTableName::getForeignKeyProperty() => $editUserObj->getId () 
				));
				
				// create a div for placing 'new' and 'discard changes button'
				$divButton = CDOMElement::create ('div', 'class:formButtons');
				
					$showButton = CDOMElement::create ('a');
					$showButton->setAttribute ('href', 'javascript:toggleForm(\'' . $form->getName () . '\', true);');
					$showButton->setAttribute ('class', 'showFormButton ' . $form->getName ());					
					$showButton->addChild (new CText (translateFN ('Nuova scheda')));
					
					$hideButton = CDOMElement::create ('a');
					$hideButton->setAttribute ('href', 'javascript:toggleForm(\'' . $form->getName () . '\');');
					$hideButton->setAttribute ('class', 'hideFormButton ' . $form->getName ());
					$hideButton->setAttribute ('style', 'display:none');
					$hideButton->addChild (new CText (translateFN ('Chiudi e scarta modifiche')));
				
				$divButton->addChild ($showButton);
				$divButton->addChild ($hideButton);
				
				$objProperty = 'tbl_' . $extraTableName;
				// create a div to wrap up all the rows of the array tbl_educationTrainig				
				$container = CDOMElement::create ('div', 'class:extraRowsContainer,id:container_' . $extraTableName);
				
				// if have 3 or more rows, add the new and discard buttons on top also
				if (count ($editUserObj->$objProperty) >= 3) {
					$divButton->setAttribute('class', $divButton->getAttribute('class').' top');
					$container->addChild (new CText ($divButton->getHtml ()));
					// reset the button class by removing top
					$divButton->setAttribute('class', str_ireplace(' top', '', $divButton->getAttribute('class')));
				}
				
				if (count ($editUserObj->$objProperty) > 0) {
					foreach ($editUserObj->$objProperty as $num => $aElement) {
						$keyFieldName = $aElement::getKeyProperty();
						$keyFieldVal = $aElement->$keyFieldName;						
						$container->addChild (new CText (UserExtraModuleHtmlLib::extraObjectRow ($aElement)));
					}
				}
				// in these cases the form is added here
				$container->addChild (CDOMElement::create('div','class:clearfix'));
				$container->addChild (new CText ($form->render()));	
				// unset the form that's going to be userd in next iteration
				unset ($form);
				$container->addChild (CDOMElement::create('div','class:clearfix'));
				// add the new and discard buttons after the container
				$divButton->setAttribute('class', $divButton->getAttribute('class').' bottom');
				$container->addChild (new CText ($divButton->getHtml ()));
			} else {
				/**
				 * place the form in the tab
				 */
				$currentForm = $tabsArray[$currTab][1];
			}

			// if a tabs container is needed, create one
			if (!isset ($tabsContainer))
			{
				$tabsContainer = CDOMElement::create ('div', 'id:tabs');
				$tabsUL = CDOMElement::create ('ul');
				$tabsContainer->addChild ($tabsUL);
			}

			// add a tab only if there's something to fill it with
			if (isset($container) || isset ($currentForm))
			{
				// create a LI
				$tabsLI = CDOMElement::create ('li');
				// add the save icon to the link
				$tabsLI->addChild (CDOMElement::create ('span', 'class:ui-icon ui-icon-disk,id:tabSaveIcon' . $currTab));
				// add a link to the div that holds tab content
				$tabLink = BaseHtmlLib::link ('#divTab' . $currTab, $tabsArray [$currTab][0]);
				if (isset($tabsArray[$currTab]['onclick'])) {
					$tabLink->setAttribute('onclick', $tabsArray[$currTab]['onclick']);					
				}
				$tabsLI->addChild ($tabLink);
				$tabsUL->addChild ($tabsLI);
				$tabContents [$currTab] = CDOMElement::create ('div', 'id:divTab' . $currTab);
				
				if (isset ($container)) {
					// add the container to the current tab
					$tabContents [$currTab]->addChild ($container);
				} else if (isset ($currentForm)) {
					// if form of current tab wants the UserExtraForm fields embedded, obey it
					if (isset($tabsArray[$currTab]['withExtra']) && $tabsArray[$currTab]['withExtra']===true) {
						UserExtraForm::addExtraControls($currentForm);
						$currentForm->fillWithArrayData($user_dataAr);						
					}
					$tabContents [$currTab]->addChild (new CText ($currentForm->getHtml()));
					unset ($currentForm);				
				}			
				$tabsContainer->addChild ($tabContents [$currTab]);
			}			
		} // end cycle through all tabs
		
		if (isset ($tabsContainer)) { 
			$data .= $tabsContainer->getHtml ();
		}
		else if (isset($form)) {			
			if (isset($extraForm)) {
				// if there are extra controls and NO tabs
				// add the extra controls to the standard form
				UserExtraForm::addExtraControls($form);
				$form->fillWithArrayData($user_dataAr);
			}
			$data .= $form->render();
		}		
		else $data = 'No form to display :(';
	} 
}

$label = translateFN('Modifica dati utente');

$divProgressBar = CDOMElement::create('div','id:progressbar');
$divProgressLabel = CDOMElement::create('div','id:progress-label');			
$divProgressBar->addChild ($divProgressLabel);			


$help = translateFN('Modifica dati utente');

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.js'
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.css'
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));

/**
 * do the form have to be submitted with an AJAX call?
 * defalut answer is true, call this method to set it to false.
 * 
 * $editUserObj->useAjax(false);
 */
if (!is_null($editUserObj)) {
	$optionsAr['onload_func']  = 'initDoc('.$maxFileSize.','. $editUserObj->getId().');';
	$optionsAr['onload_func'] .= 'initUserRegistrationForm('.(int)(isset($tabsContainer)).', '.(int)$editUserObj->saveUsingAjax().');';
} else $optionsAr = null;

//$optionsAr['onload_func'] = 'initDateField();';
$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

/*
 * Display error message  if the password is incorrect
 */
if(isset($_GET['message']))
{
   $help= $_GET['message'];
}
   
if(isset($_SESSION['sess_id_course_instance']))
{
    $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
}
 else {
    $last_access=$userObj->get_last_accessFN(null,"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
    $user_level=translateFN('Nd');
 }
 if($last_access=='' || is_null($last_access)){
    $last_access='-';
}

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'course_title' => translateFN('Modifica profilo'),
    'data' => $data,
    'last_visit' => $last_access,
    'help' => $help,
	'user_avatar'=>$avatar->getHtml(),		
	'user_modprofilelink' => $userObj->getEditProfilePage(),
	
);



/**
 * If it's a switcher the renderer is called by switcher/edit_user.php
 * UNIMC Only:
 * If it's a tutor the renderer is called by tutor/edit_user.php
 */
if ($userObj->getType() != AMA_TYPE_SWITCHER && $userObj->getType() != AMA_TYPE_TUTOR) {
	ARE::render($layout_dataAr, $content_dataAr,NULL, $optionsAr);
}
