<?php
/**
 * Client specific configuration file.
 *
 * PHP version >= 5.0
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright   (c) 2009-2010 Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */

if (!MULTIPROVIDER)
{
	/**
	 * ID of the public course to get the latest news
	 */
	define ('PUBLIC_COURSE_ID_FOR_NEWS', 1);
	/**
	 * How many news to get from the above mentioned course
	*/
	define ('NEWS_COUNT', 3);
	/**
	 * Provider default language
	 */
	define ('PROVIDER_LANGUAGE','it');
	/**
	 * URL
	 * DO NOT REMOVE the trailing // *js_import*
	 */
	define('HTTP_ROOT_DIR','http://sampleprovider.localhost/ada20'); // *js_import*
	define('PORTAL_NAME','ADA 2.1 SAMPLE PROVIDER');
}

/**
 * Import user API endpoint configuration
 */
define ('USERIMPORT_API_URL',	''); // USER IMPORT API endpoint here
define ('USERIMPORT_API_USER',	''); // USER IMPORT API endpoint login username
define ('USERIMPORT_API_PASSWD',''); // USER IMPORT API endpoint login password

/**
 * ESSE3 API endpoint configuration
 */
define ('ESSE3_URL',			''); // ESSE3 API endpoint
define ('ESSE3_LOGIN',			''); // ESSE3 API login username
define ('ESSE3_PASSWD',			''); // ESSE3 API login password

/**
 * Survey user API endpoint configuration
 */
define ('SURVEY_API_URL',	''); // SURVEY API endpoint here
define ('SURVEY_API_USER',	''); // SURVEY API endpoint login username
define ('SURVEY_API_PASSWD',''); // SURVEY API endpoint login password

/**
 * Provider holidays feed, to be show on fullcalendar. Sample is italian holidays feed
 */
define ('GCAL_HOLIDAYS_FEED','http://www.google.com/calendar/feeds/e327d6lm6r2kb555ce82ll4sbs@group.calendar.google.com/public/basic');	

/*
 * Maximum number of appointment proposal the tutor can make
 */
define ('MAX_PROPOSAL_COUNT',2);

/**
 *
 * @name SAMPLE_DB_TYPE
 */
define('SAMPLE_DB_TYPE',  'mysql');

/**
 *
 * @name SAMPLE_DB_NAME
 */
define('SAMPLE_DB_NAME',  'ada_provider_SAMPLE');

/**
 *
 * @name SAMPLE_DB_USER
 */
define('SAMPLE_DB_USER',  'ada_db_SAMPLE');

/**
 *
 * @name SAMPLE_DB_PASS
 */
define('SAMPLE_DB_PASS',  'SAMPLE');

/**
 *
 * @name SAMPLE_DB_HOST
 */
define('SAMPLE_DB_HOST',  'localhost');

/**
 *
 * @name SAMPLE_TIMEZONE
 */
define('SAMPLE_TIMEZONE',  'Europe/Rome');
