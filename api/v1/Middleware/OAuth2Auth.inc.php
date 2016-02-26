<?php
/**
 * OAuth2Auth.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;

/**
 * ADA's own inclusions
 */
require_once realpath (dirname (__FILE__)) . '/../../../config_path.inc.php';
require_once realpath (dirname (__FILE__)) . '/../../OAuth2/Autoloader.php';

/**
 * Middleware that does the OAuth2 access_token verifications,
 * will set the response status to 401 if the access_token is
 * not authorized
 *  
 * @author giorgio
 */
class OAuth2Auth extends \Slim\Middleware {
	
	private $_dsn      ;
	private $_username ;
	private $_password ;
	
	/**
	 * user ID associated with token
	 * @var int
	 */
	private $_authUserID = null;

	public function __construct() {
		$this->_dsn      = ADA_COMMON_DB_TYPE.':dbname='.ADA_COMMON_DB_NAME.';host='.ADA_COMMON_DB_HOST;
		$this->_username = ADA_COMMON_DB_USER;
		$this->_password = ADA_COMMON_DB_PASS;
	}
	
	/**
	 * Gets the userid associated with the access_token
	 * 
	 * @return number
	 */
	public function getAuthUserID() {
		return intval($this->_authUserID);
	}
	
	/**
	 * checks if a valid access_token has been passed
     *
	 * @see \Slim\Middleware::call()
	 */
	public function call()
	{
		\OAuth2_Autoloader::register();
		$storage = new \OAuth2_Storage_ADA(array(
				'dsn' => $this->_dsn, 
				'username' => $this->_username,
				'password' => $this->_password));
		
		// Pass a storage object or array of storage objects to the OAuth2 server class
		$server = new \OAuth2_Server($storage);
		
		// Add the "Client Credentials" grant type (it is the simplest of the grant types)
		$server->addGrantType(new \OAuth2_GrantType_ClientCredentials($storage));
		
		// Add the "Authorization Code" grant type (this is where the oauth magic happens)
		// $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($storage));
		
		// Handle a request for an OAuth2.0 Access Token and send the response to the client
		$request = \OAuth2_Request::createFromGlobals();
		if (!$server->verifyResourceRequest($request,new \OAuth2_Response())) {
			// uncomment below to send $server's own response.
			// we want to use SLIM framework here
			// $server->getResponse()->send();
			$this->app->response->setStatus(401);
			$this->app->response->setBody('access_token is invalid or not authorized');
		} else {
			$token = $server->getAccessTokenData($request, new \OAuth2_Response());
			$this->_authUserID = $token['user_id'];
			$this->next->call();
		}
	}
}
?>