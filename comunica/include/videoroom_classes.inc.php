<?php
/**
 * videoroom classes
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		node_classes
 * @version		0.1
 */

class videoroom 
{
	var $id;
	var $id_room;
	var $id_istanza_corso;
	var $id_tutor;
	var $tipo_videochat;
	var $descrizione_videochat;
	var $tempo_avvio; 
	var $tempo_fine;
	var $full;
	
	var $client_user; 
	var $session_id;
	var $login;
	var $error_openmeetings;
	
	var $client_room;
	var $roomTypes;
	var $rooms; // elenco stanze disponibili sul server
	var $link_to_room;
	var	$room_properties;
	var $list_room; // elenco stanze disponibili sul server
	 
	
public function __construct($id_course_instance=""){
    $dh            =   $GLOBALS['dh'];
    $error         =   $GLOBALS['error'];
    $debug         =   isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
    $root_dir      =   $GLOBALS['root_dir'];
    $http_root_dir =   $GLOBALS['http_root_dir'];
    
}    

public function videoroom_info($id_course_instance,$tempo_avvio=NULL, $interval=NULL){
    $dh            =   $GLOBALS['dh'];
    $error         =   $GLOBALS['error'];
    $debug         =   isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
    $root_dir      =   $GLOBALS['root_dir'];
    $http_root_dir =   $GLOBALS['http_root_dir'];   
    $video_roomAr = $dh->get_videoroom_info($id_course_instance, $tempo_avvio, $interval);

    if (AMA_DataHandler::isError($video_roomAr) || !is_array($video_roomAr)) {
      // FIXME: prima restituiva una stringa di testo
      $this->full = 0;
      
    }
    else {
    	$this->id_room = $video_roomAr['id_room'];
    	$this->id_tutor = $video_roomAr['id_tutor'];
    	$this->id = $video_roomAr['id'];
    	$this->id_istanza_corso = $video_roomAr['id_istanza_corso'];
    	$this->descrizione_videochat = $video_roomAr['descrizione_videochat'];
    	$this->tempo_avvio = $video_roomAr['tempo_avvio'];
    	$this->tempo_fine = $video_roomAr['tempo_fine'];
    	$this->tipo_videochat = $video_roomAr['tipo_videochat'];
    	$this->full = 1;
    }
}

/*
 * Creazione videochat in openmeetings e registrazione dei dati nel DB locale
 */
public function add_openmeetings_room($name="service", $sess_id_course_instance, $sess_id_user, $comment="Inserimento automatico via ADA",$num_user=4) {
	$dh            =   $GLOBALS['dh'];
    $error         =   $GLOBALS['error'];
    $debug         =   $GLOBALS['debug'];
    $root_dir      =   $GLOBALS['root_dir'];
    $http_root_dir =   $GLOBALS['http_root_dir'];
	
    
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;

	$room_type = intval(CONFERENCE_TYPE);
//	$room_type = intval(AUDIENCE_TYPE);
        $ispublic = ROOM_IS_PUBLIC;
        $allowUserQuestions = ROOM_ALLOW_QUESTIONS;
        $isAudioOnly = ROOM_AUDIO_ONLY;
        $hideTopBar = ROOM_HIDE_TOP_BAR;
        $hideChat = ROOM_HIDE_CHAT;
        $hideActivitiesAndActions = ROOM_HIDE_ACTIONS;
        $hideFilesExplorer = ROOM_HIDE_FILE_EXPLORER;
        $hideActionsMenu = ROOM_ACTION_MENU;
        $hideScreenSharing = ROOM_HIDE_SCREEN_SHARING;
        $hideWhiteboard = ROOM_HIDE_WHITEBOARD;
                
        /*
         * DEPRECATED
         *
	$videoPodWidth = VIDEO_POD_WIDTH;
	$videoPodHeight = VIDEO_POD_HEIGHT;
	$videoPodXPosition = VIDEO_POD_X_POSITION;
	$videoPodYPosition = VIDEO_POD_y_POSITION;
	$moderationPanelXPosition = MODERATION_PANEL_X_POSITION;
	$showWhiteBoard = SHOW_WHITE_BOARD;
	$whiteBoardPanelXPosition = WHITE_BOARD_PANEL_X_POSITION;
	$whiteBoardPanelYPosition = WHITE_BOARD_PANEL_Y_POSITION;
	$whiteBoardPanelHeight = WHITE_BOARD_PANEL_HEIGHT;
	$whiteBoardPanelWidth = WHITE_BOARD_PANEL_WIDTH;
	$showFilesPanel = SHOW_FILES_PANEL;
	$filesPanelXPosition = FILES_PANEL_X_POSITION;
	$filesPanelYPosition = FILES_PANEL_Y_POSITION;
	$filesPanelHeight = FILES_PANEL_HEIGHT;
	$filesPanelWidth = FILES_PANEL_WIDTH;
         * 
         * DEPRECATED
         */
	
	
	//Create the SoapClient object
	$this->client_room = new SoapClient("http://".$host.$port."/".$dir."/services/RoomService?wsdl");

        /*
        $addRoomWithModerationParams = array(
                'SID' => $this->session_id,
                'name' => $name,
                'roomtypes_id' => intval($room_type), //1,
                'comment' => $comment,
                'numberOfPartizipants' => intval($num_user),
                'ispublic' => $ispublic,
                'appointment'=> FALSE,
                'isDemoRoom'=> FALSE,
                'demoTime' => 0,
                'isModeratedRoom'=> TRUE
        );
	$this->resultAddRoom = $this->client_room->addRoomWithModeration($addRoomWithModerationParams);
	$this->id_room = $this->resultAddRoom->return;
         * 
         */

        $addRoomWithModerationHideOptionsParams = array(
            'SID' => $this->session_id,
            'name' => $name,
            'roomtypes_id' => intval($room_type), 
            'comment' => $comment,
            'numberOfPartizipants' => intval($num_user),
            'ispublic' => $ispublic,
            'appointment'=> FALSE,
            'isDemoRoom'=> FALSE,
            'demoTime' => 0,
            'isModeratedRoom'=> TRUE,
            'allowUserQuestions' => $allowUserQuestions,
            'isAudioOnly' => $isAudioOnly,
            'hideTopBar' => $hideTopBar,
            'hideChat' => $hideChat,
            'hideActivitiesAndActions' => $hideActivitiesAndActions,
            'hideFilesExplorer' => $hideFilesExplorer,
            'hideActionsMenu' => $hideActionsMenu,
            'hideScreenSharing' => $hideScreenSharing,
            'hideWhiteboard' => $hideWhiteboard
            
        );
	$this->resultAddRoom = $this->client_room->addRoomWithModerationQuestionsAudioTypeAndHideOptions($addRoomWithModerationHideOptionsParams);
	$this->id_room = $this->resultAddRoom->return;        
        /*
         * forza apertura della stanza
         */
        $closeParameterAr = array (
                'SID' => $this->session_id,
                'room_id'=> $this->id_room,
                'status'=> 0
        );
        $this->closeRoom = $this->client_room->closeRoom($closeParameterAr);
        
        	
	$interval = 60*60;
    $videoroom_dataAr['id_room'] = $this->id_room; 
    $videoroom_dataAr['id_istanza_corso'] = $sess_id_course_instance;
    $videoroom_dataAr['id_tutor']= $sess_id_user;
    $videoroom_dataAr['tipo_videochat'] = $room_type;
    $videoroom_dataAr['descrizione_videochat'] = $name;
    $videoroom_dataAr['tempo_avvio'] = time();
    $videoroom_dataAr['tempo_fine'] = time() + $interval;
      
    $videoroom_data = $dh->add_videoroom($videoroom_dataAr);      
	
}

public function list_rooms() {
	
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;
	
	//Create the SoapClient object
	$this->client_room = new SoapClient("http://".$host.$port."/".$dir."/services/RoomService?wsdl");
	
	$rooms_params   = array(
	    'SID' => $this->session_id,
	    'start' => 1,
	    'max' => 999,
            'orderby' => "name",
            'asc' => 1
	);
	
	$this->list_rooms = $this->client_room->getRooms($rooms_params);
	
}

public function get_room($id_room) {
	
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;
	
	//Create the SoapClient object
	$this->client_room = new SoapClient("http://".$host.$port."/".$dir."/services/RoomService?wsdl");
	
	$rooms_params   = array(
	    'SID' => $this->session_id,
	    'rooms_id' => $id_room
	);
	
	$this->room_properties = $this->client_room->getRoomById($rooms_params);
//	$this->room_properties = $this->client_room->getRoomWithCurrentUsersById($rooms_params);
	
	
}

public function server_login() {
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;
	
	$this->client_user = new SoapClient("http://".$host.$port."/".$dir."/services/UserService?wsdl");
	
	//get  new session_id  for accessing and creating rooms
	$result = $this->client_user->getSession();
	$this->session_id = $result->return->session_id;

	//login as admin to create and access rooms
	
	$login_params   = array(
	    'SID' => $this->session_id,
	    'username' => OPENMEETINGS_ADMIN,
	    'userpass' => OPENMEETINGS_PASSWD,
	);
	$this->login = $this->client_user->loginUser($login_params);
	if ($this->login->return <= 0) {
//		echo "<br>Login failed<br>";
		$error_params = array(
			'SID' => $this->session_id,
			'errorid' => $this->login->return,
			'language_id' => 13,
		);
		$this->error_opnemeetings = $this->client_user->getErrorByCode($error_params);
		//print_r($error);
	
	} else {
//		echo "<br>Login successful<br>";
//		echo "<br>". $login->return ."<br>";
	}
}

public function room_access($username,$nome,$cognome,$user_email,$sess_id_user,$id_profile) {
	
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;
	
	//Create the SoapClient object
	$this->client_room = new SoapClient("http://".$host.$port."/".$dir."/services/RoomService?wsdl");

        $becomeModeratorAsInt = 0; // 0 = no Moderator 1 = Moderator @todo impostare a moderatore se practitioner
        $allowRecording = 0; // 0 means don't allow Recording, 1 means allow Recording
        if ($id_profile == AMA_TYPE_TUTOR ) {
            $becomeModeratorAsInt = 1;
            $allowRecording = 1;
        }
	$room_id = $this->id_room;
        $externalUserId = $sess_id_user;
        $externalUserType = "ADA"; // potrebbe essere preso da $userObj->type
        $showAudioVideoTestAsInt = 1; // 0 = no audio/video test
        if ( OPENMEETINGS_VERSION > 0) {
            $user_params   = array(
                'SID' => $this->session_id,
                'username' => $username,
                'firstname' => $nome,
                'lastname' => $cognome,
		'profilePictureUrl' => "",
		'email' => $user_email,
                'externalUserId' => $externalUserId,
                'externalUserType' => $externalUserType,
                'room_id' => $room_id,
                'becomeModeratorAsInt' => $becomeModeratorAsInt,
                'showAudioVideoTestAsInt' => $showAudioVideoTestAsInt
            );
                /* @var $secureHash <array> needed to access openmeetings room*/
        	$this->secureHash = $this->client_user->setUserObjectAndGenerateRoomHashByURL($user_params);
                $secureHash = $this->secureHash->return;
            
             /*   
             $user_params_flags   = array(
                'SID' => $this->session_id,
                'username' => $username,
                'firstname' => $nome,
                'lastname' => $cognome,
		'profilePictureUrl' => "",
		'email' => $user_email,
                'externalUserId' => $externalUserId,
                'externalUserType' => $externalUserType,
                'room_id' => $room_id,
                'becomeModeratorAsInt' => $becomeModeratorAsInt,
                'showAudioVideoTestAsInt' => $showAudioVideoTestAsInt,
                'allowRecording' => $allowRecording
            );

            
        	$this->secureHash = $this->client_user->setUserObjectAndGenerateRoomHashByURLAndRecFlag($user_params_flags);
                $secureHash = $this->secureHash->return;
              * 
              */
                

        } else {
            $user_params   = array(
        	'SID' => $this->session_id,
                'username' => $username,
                'firstname' => $nome,
                'lastname' => $cognome,
		'profilePictureUrl' => "",
		'email' => $user_email
            );
            $setUser = $this->client_user->setUserObject($user_params);

        }
	//echo "<br> Istanza root: ";
	//print_r($client_room);
	
//	$type_params   = array(
//	    'SID' => $this->session_id,
//	    'rooms_id' => $this->id_room
//	);
//	$this->roomTypes = $this->client_room->getRoomById($type_params);
	

	
	/*
	 * LINK A STANZA
	 */
	$language= ROOM_DEFAULT_LANGUAGE;
	$sess_lang = $_SESSION['sess_user_language'];
	$videochat_lang = "VIDEOCHAT_LANGUAGE_".strtoupper($sess_lang);
	if (defined($videochat_lang)) {
		$language = constant($videochat_lang); 
	}
        if ( OPENMEETINGS_VERSION > 0) {
           $this->link_to_room = "http://".$host.$port."/".$dir."/?secureHash=".$secureHash."&language=".$language;
//           print_r($this->link_to_room); die();
//           $this->link_to_room = "http://".$host.$port."/".$dir."/?secureHash=".$secureHash."&scopeRoomId=".$this->id_room."&language=".$language."&user_id=".$sess_id_user;
        } else {
            $this->link_to_room = "http://".$host.$port."/".$dir."/main.lzx.lzr=swf8.swf?roomid=".$this->id_room."&sid=".$this->session_id."&language=".$language;
       }
//       print_r($this->link_to_room);
       
}

public function delete_room($id_room) {
	$dh = $GLOBALS['dh'];
	$host = OPENMEETINGS_HOST;
	$port = OPENMEETINGS_PORT;
	$dir = OPENMEETINGS_DIR;
	
	//Create the SoapClient object
	$this->client_room = new SoapClient("http://".$host.$port."/".$dir."/services/RoomService?wsdl");

	$params   = array(
    	'SID' => $this->session_id,
    	'rooms_id' => $id_room
	);
	
	$result_openmeetings = $this->client_room->deleteRoom($params);
	if ($result_openmeetings->return == $id_room) { // if deleted ok in openmeetings delete in DB too
		$result = $dh->delete_videoroom($id_room);
	}
	
} 

}
?>