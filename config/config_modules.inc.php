<?php

	//defines for modules test
	define('MODULES_TEST_PATH', MODULES_DIR.'/test');
	if (file_exists(MODULES_TEST_PATH.'/index.php') 
	 && file_exists(MODULES_TEST_PATH.'/edit_test.php')
	 && file_exists(MODULES_TEST_PATH.'/tutor.php')) {
		require_once(MODULES_TEST_PATH.'/config/config.inc.php');

		define('MODULES_TEST', true);
		define('MODULES_TEST_HTTP', HTTP_ROOT_DIR.'/modules/test');
	}
	else {
		define('MODULES_TEST', false);
	}
	
	//defines for module newsletter
	define('MODULES_NEWSLETTER_PATH', MODULES_DIR.'/newsletter');
	if (file_exists(MODULES_NEWSLETTER_PATH.'/index.php'))
	 {
		require_once(MODULES_NEWSLETTER_PATH.'/config/config.inc.php');
	
		define('MODULES_NEWSLETTER', true);
		define('MODULES_NEWSLETTER_HTTP', HTTP_ROOT_DIR.'/modules/newsletter');
	}
	else {
		define('MODULES_NEWSLETTER', false);
	}
	
	//defines for module service-complete
	define('MODULES_SERVICECOMPLETE_PATH', MODULES_DIR.'/service-complete');
	if (file_exists(MODULES_SERVICECOMPLETE_PATH.'/index.php'))
	{
		require_once(MODULES_SERVICECOMPLETE_PATH.'/config/config.inc.php');
	
		define('MODULES_SERVICECOMPLETE', true);
		define('MODULES_SERVICECOMPLETE_HTTP', HTTP_ROOT_DIR.'/modules/service-complete');
	}
	else {
		define('MODULES_SERVICECOMPLETE', false);
	}
	
	//defines for module lex
	define('MODULES_LEX_PATH', MODULES_DIR.'/lex');
	if (file_exists(MODULES_LEX_PATH.'/index.php'))
	{
		require_once(MODULES_LEX_PATH.'/config/config.inc.php');
	
		define('MODULES_LEX', true);
		define('MODULES_LEX_HTTP', HTTP_ROOT_DIR.'/modules/lex');
	}
	else {
		define('MODULES_LEX', false);
	}
	
?>
