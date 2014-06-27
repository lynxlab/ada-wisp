<?php
/**
 * Openmeetings specific configuration file.
 * 
 * @author
 * @version
 * @package
 * @license
 * @copyright (c) 2009 Lynx s.r.l.
 */

/**
 * 
 * @name OPENMEETINGS DATA SERVER 
 */

define('OPENMEETINGS_HOST',  'URL');
define('OPENMEETINGS_PORT',  ':5080');
define('OPENMEETINGS_ADMIN',  'user');
define('OPENMEETINGS_PASSWD',  '');
define('OPENMEETINGS_DIR',  'openmeetings');



/**
 * 
 * @name OPENMEETINGS DEFAULT DATA ROOM 
 */

define('ROOM_DEFAULT_LANGUAGE',  '4');
define('ROOM_IS_PUBLIC',  FALSE);
define('ROOM_ALLOW_QUESTIONS',  false);
define('ROOM_AUDIO_ONLY',  false);
define('ROOM_HIDE_TOP_BAR',  true);
define('ROOM_HIDE_CHAT',  false);
define('ROOM_HIDE_ACTIONS',  true);
define('ROOM_HIDE_FILE_EXPLORER',  false);
define('ROOM_ACTION_MENU',  false);
define('ROOM_HIDE_SCREEN_SHARING',  false);
define('ROOM_HIDE_WHITEBOARD',  false);


define('VIDEO_POD_WIDTH',  '355');
define('VIDEO_POD_HEIGHT',  '560');
define('VIDEO_POD_X_POSITION',  '2');
define('VIDEO_POD_y_POSITION',  '2');
define('MODERATION_PANEL_X_POSITION',  '400');
define('SHOW_WHITE_BOARD',  'true');
define('WHITE_BOARD_PANEL_X_POSITION',  '360');
define('WHITE_BOARD_PANEL_Y_POSITION',  '2');
define('WHITE_BOARD_PANEL_HEIGHT',  '560');
define('WHITE_BOARD_PANEL_WIDTH',  '600');
define('SHOW_FILES_PANEL',  'false');
define('FILES_PANEL_X_POSITION',  '2');
define('FILES_PANEL_Y_POSITION',  '284');
define('FILES_PANEL_HEIGHT',  '310');
define('FILES_PANEL_WIDTH',  '270');

// ***********
define('CONFERENCE_TYPE','1');
define('AUDIENCE_TYPE','2'); 

//*******
define('FRAME_WIDTH','1000');
define('FRAME_HEIGHT','600');

define('VIDEOCHAT_LANGUAGE_BG', '30');
define('VIDEOCHAT_LANGUAGE_EN', '1');
define('VIDEOCHAT_LANGUAGE_ES', '8');
//define('VIDEOCHAT_LANGUAGE_ES', '6'); // versione 0
define('VIDEOCHAT_LANGUAGE_IS', '1');
define('VIDEOCHAT_LANGUAGE_IT', '5');
define('VIDEOCHAT_LANGUAGE_RO', '1');
define('VIDEOCHAT_LANGUAGE_FR', '4');
define('VIDEOCHAT_LANGUAGE_DE', '2');

define('DATE_CONTROL',FALSE);

define('OPENMEETINGS_VERSION','3.02');

?>
 
