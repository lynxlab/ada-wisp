<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * LDAP login provider implementation
 */
class ldapLogin extends AbstractLogin
{
	const INVALID_USERNAME_EXECEPTION_CODE = 49;
	
	/**
	 * class for managing options data
	 */
	const MANAGEMENT_CLASS = 'ldapManagement';
	
	/**
	 * performs user login using an LDAP server
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		$loginResult = null;
		$errorMessages = array();
		$allOptions = $this->loadOptions();

		if (!is_null($allOptions)) {
			if ($allOptions['optionscount']<=1) $allOptions = array ($allOptions);
			unset($allOptions['optionscount']);
			
			// check if user to be authenticate is already in the ADA db
			$ADAuser = MultiPort::findUserByUsername($name);
			if (is_object($ADAuser) && $ADAuser instanceof ADALoggableUser) {
				// if found, try to get the last successfull login options ID
				$lastSuccessfulID = $this->getLastSuccessfulOptionIDForUser ($ADAuser->getId());				
				/**
				 * move found option id to top of array
				 */
				if ($lastSuccessfulID!==false && isset($allOptions[$lastSuccessfulID]) && is_array($allOptions[$lastSuccessfulID])) {
					$tmp = array($lastSuccessfulID=>$allOptions[$lastSuccessfulID]);
					unset ($allOptions[$lastSuccessfulID]);
					$allOptions = $tmp + $allOptions;
				}
			}
			
			foreach ($allOptions as $option_id=>$options) {
				// disabled options are not returned at all by loadOptions
				$loginResult = $this->doLoginAttempt($name, $pass, $remindMe, $language, $options, $ADAuser);
				if (is_object($loginResult) && $loginResult instanceof ADALoggableUser) {
					$this->setSuccessfulOptionsID($option_id);
					return $loginResult;
				} else if ((is_object($loginResult)) && ($loginResult instanceof Exception)) {
					if(!in_array($loginResult->getMessage(), $errorMessages)) $errorMessages[] = $loginResult->getMessage();
				}
			}
		}
		if (count($errorMessages)==0) {
			$errorMessages[] = translateFN('Nessun servizio LDAP configurato o attivo');
		}
		return new Exception(implode('<br/>', $errorMessages));
	}
	
	private function doLoginAttempt($name, $pass, $remindMe, $language, $options, $foundADAuser=null)
	{
		try {
			/**
			 * If invalid name or password, throw exception
			 */
			if ($name === false || $pass === false) throw new Exception(null,self::INVALID_USERNAME_EXECEPTION_CODE);
			
			/**
			 * check LDAP configuration in module's option table
			 */
			if (!is_null($options) && is_array($options) && count($options)>0) {
				// mandatory fields
				$mandatoryOptions = array(
						'host' => 'Impostare l\'host LDAP',
						'authdn'  => 'Impostare il dn di autenticazione LDAP',
						'basedn' => 'Impostare il dn di ricerca in LDAP',
						'usertype' => 'Specificare il ruolo utente WISP',
						// UNIMC SPECIFIC
						'authuser' => 'Impostare un username per l\'autenticazione',
						'authpwd'  => 'Impostare una password per l\'autenticazione'
				);
				
				foreach ($mandatoryOptions as $optionName=>$errorMessage) {
					if (!array_key_exists($optionName, $options) || strlen($options[$optionName])<=0) {
						$errorMessage = translateFN($errorMessage) .
										'<br/>(key=\''.$optionName.'\' '.translateFN('nelle opzioni').')';
						throw new Exception($errorMessage);
					}
				}
			} else throw new Exception(translateFN('Impossibile caricare la configurazione LDAP'));
			// connect to host
			$handle = ldap_connect($options['host']);
			// set options
			ldap_set_option($handle, LDAP_OPT_PROTOCOL_VERSION, 3 );
			ldap_set_option($handle, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($handle, LDAP_OPT_NETWORK_TIMEOUT,  30); /* 30 second timeout */
			
			/**
			 * UNIMC specific code:
			 * 
			 * bind the the options/authuser and options/authpwd to the options/authdn
			 * and perform a search for the login user name in the options/basedn
			 */
			$foundDN = null;
			$bind = ldap_bind($handle, 'uid='.$options['authuser'].','.$options['authdn'], $options['authpwd']);			
			$result = ldap_search($handle, $options['basedn'], "uid=".$name);
			if ($result!==false) {
				// if something has been found, check that it's an alias
				$entries = ldap_get_entries($handle, $result);
				$lookfor = 'aliasedobjectname';
				foreach ($entries[0] as $key=>$entry) {
					if ($key===$lookfor && $entries[0][$lookfor]['count']===1) {
						$foundDN = $entries[0][$key][0];
					}
				}
			}
			else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
			
			// use the found DN from now on
			if (is_null($foundDN)) throw new Exception($name.' '.translateFN('non trovato'));
			else {
				/**
				 * split the found dn in its components
				 */
				$DNarray = ldap_explode_dn ($foundDN,0);
				if (is_array($DNarray) && count($DNarray)>0) {
					unset($DNarray['count']);
					foreach ($DNarray as $key=>$dnElement) {
						if (stripos($dnElement, 'uid=')===0) {
							$foundUID = $dnElement;
							unset ($DNarray[$key]);
						}
					}
					$foundRDN = implode(',', $DNarray);
				}
				/**
				 * here $foundUID is the found uid and $foundRDN is the found RDN
				 */
			}
			
			// this will output a warning in the webserver log on failure
			// WAS:
			// $bind = ldap_bind($handle, 'uid='.$name.','.$options['authdn'], $pass);
			// FOR UNIMC IS:
			$bind = ldap_bind($handle, $foundUID.','.$foundRDN, $pass);
			
			if ($bind !==false) {
				/**
				 * For UNIMC, don't look if user is already in ADA DB
				 * just load its data and create/update them
				 */
				
				// WAS:
				// $result = ldap_search($handle, $options['authdn'], "uid=".$name);
				// FOR UNIMC IS:
				$result = ldap_search($handle, $foundUID.','.$foundRDN, $foundUID);
				/**
				 * If $results is false, throw an exception
				 */
				if ($result!==false) $entries = ldap_get_entries($handle, $result);
				else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
				
				if ($entries!==false && is_array($entries) && count($entries)>0) {
					$entries = $entries[0];
					/**
					 * If user uid is listed in the memberUid attributes
					 * for the basedn than it's safe to say that his type is $options['usertype']
					 */
					if (!is_null($entries)) {
						$namefilter = '(&('.$foundUID.'))';							
						if  (isset($options['filter']) && strlen($options['filter'])>0) {
							// extract filter up to last ')' character
							$substr = substr($options['filter'], 0,strrpos($options['filter'], ')'));
							// concatenate $namefilter to passed filter and restore the last ')'
							$query = $substr.$namefilter.')';
						} else $query = $foundUID;
						
						$groupres = ldap_search($handle, $foundUID.','.$foundRDN, $query);
						if ($groupres!==false) $groupentries = ldap_get_entries($handle, $groupres); 
						else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
						
						if ($groupentries!==false && is_array($groupentries) && count($groupentries)>0) {
							if($groupentries['count']>0) {
								// all went ok here: user has been found, user data has been loaded
								// and user memberUid was found on the passed basedn, create ADA user 
								$userType = $options['usertype'];
								/**
								 * build user array
								 */
								$adaUser = array(
										'nome' => $entries['givenname'][0],
										'cognome' => $entries['sn'][0],
										'username' => $entries['uid'][0],
										'tipo' => $options['usertype'],
										'cap' => '',
										'avatar' => '',
										'birthcity' => '',
										// UNIMC SPECIFIC
										'email' => (isset($entries['mail'][0])) ? $entries['mail'][0] : 'nobody',
										'matricola' => (isset($entries['matricola'][0])) ? $entries['matricola'][0] : '',
										'codice_fiscale' => (isset($entries['cf'][0])) ? $entries['cf'][0] : ''
								);
								
								if (!is_null($foundADAuser)) {
									$adaUser = array_merge($foundADAuser->toArray(), $adaUser);
								}
								
								if (isset($handle) && !is_null($handle)) ldap_unbind($handle);
								return $this->addADAUser($adaUser);
							}
						}
						
						return new Exception(translateFN('Utente non trovato nel dn fornito per').' '.$options['name']);
					}
					
				}
				
				/**
				 * At this point, either the $userObj was already in
				 * ADA DB or had just been created by the above code
				 */
				if (is_object($userObj) && $userObj instanceof ADALoggableUser) {
					return $userObj;
				}
			} else {
				throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
			}
		} catch (Exception $e) {
			if (!is_null($handle)) ldap_unbind($handle);
			// 'Invalid credentials' (code:49)  gets ADA's own message as text
			if ($e->getCode()==self::INVALID_USERNAME_EXECEPTION_CODE) {
				return new Exception(translateFN("Username  e/o password non valide"), self::INVALID_USERNAME_EXECEPTION_CODE);
			}
			return new Exception($e->getMessage().' '.translateFN('di').' '.$options['name']);
		}
	}
	
	/**
	 * generate HTML for login provider configuration page
	 */
	public function generateConfigPage() {
		$configIndexDIV = CDOMElement::create('div','id:configindex');
		$newButton = CDOMElement::create('button');
		$newButton->setAttribute('class', 'newButton top tooltip');
		$newButton->setAttribute('title', translateFN('Clicca per creare un nuova fonte'));
		$newButton->setAttribute('onclick', 'javascript:editOptionSet(null);');
		$newButton->addChild (new CText(translateFN('Nuova Fonte')));
		$configIndexDIV->addChild($newButton);
		$tableOutData = array();
		$optionSetList = $this->getAllOptions();
		
		if (!AMA_DB::isError($optionSetList)) {
		
			$labels = array (translateFN('nome'), translateFN('host'),  translateFN('stato'),
					translateFN('azioni'));
			foreach ($optionSetList as $i=>$elementArr) {
				$isEnabled = intval($elementArr['enabled'])===1;
				unset ($elementArr['enabled']);
				unset ($elementArr['order']);
				
				$keys = array_keys($elementArr);
				$values = array_values($elementArr);
				
				$links = array();
				$linksHtml = "";
		
				for ($j=0;$j<5;$j++) {
					switch ($j) {
						case 0:
							$type = 'edit';
							$title = translateFN('Modifica Fonte');
							$link = 'editOptionSet('.$i.');';
							break;
						case 1:
							$type = 'delete';
							$title = translateFN ('Cancella Fonte');
							$link = 'deleteOptionSet($j(this), '.$i.' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
							break;
						case 2:
							$type = ($isEnabled) ? 'disable' : 'enable';
							$title = ($isEnabled) ? translateFN('Disabilita') : translateFN('Abilita');
							$link = 'setEnabledOptionSet($j(this), '.$i.', '.($isEnabled ? 'false' : 'true').');';
							break;
						case 3:
							$type = 'up';
							$title = translateFN('Sposta su');
							$link = 'moveOptionSet($j(this),'.$i.',-1);';
							break;
						case 4:
							$type = 'down';
							$title = translateFN('Sposta giu');
							$link = 'moveOptionSet($j(this),'.$i.',1);';
							break;
					}
		
					if (isset($type)) {
						$links[$j] = CDOMElement::create('li','class:liactions');
						$linkshref = CDOMElement::create('button');
						$linkshref->setAttribute('onclick','javascript:'.$link);
						$linkshref->setAttribute('class', $type.'Button tooltip');
						$linkshref->setAttribute('title',$title);
						$links[$j]->addChild ($linkshref);
						// unset for next iteration
						unset ($type);
					}
				}
				if (!empty($links)) {
					$linksul = CDOMElement::create('ul','class:ulactions');
					foreach ($links as $link) $linksul->addChild ($link);
					$linksHtml = $linksul->getHtml();
				} else $linksHtml = '';
		
				$tableOutData[$i] = array (
						$labels[0]=>$elementArr['name'],
						$labels[1]=>$elementArr['host'],
						$labels[2]=>(($isEnabled) ? translateFN('Abilitata') : translateFN('Disabilitata') ),
						$labels[3]=>$linksHtml);
			}
		
			$OutTable = BaseHtmlLib::tableElement('id:complete'.strtoupper(get_class($this)).'List',
					$labels,$tableOutData,'',translateFN('Elenco delle fonti '.strtoupper($this->loadProviderName())));
			$configIndexDIV->addChild($OutTable);
		
			// if there are more than 10 rows, repeat the add new button below the table
			if (count($optionSetList)>10) {
				$bottomButton = clone $newButton;
				$bottomButton->setAttribute('class', 'newButton bottom tooltip');
				$configIndexDIV->addChild($bottomButton);
			}
		} // if (!AMA_DB::isError($optionSetList))
		return $configIndexDIV;
	}
}
