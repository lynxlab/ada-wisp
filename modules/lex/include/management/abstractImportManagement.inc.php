<?php
/**
 * Lex Management Class
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing the Lex Module import
 *
 * @author giorgio
 */

require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';
require_once MODULES_LEX_PATH . '/include/management/jexManagement.inc.php';

abstract class importManagement 
{
	/**
	 * The imported file name
	 * @var string
	 */
	protected $_importFileName;
	
	/**
	 * The destination dir to unzip and to look for files
	 * @var string
	 */
	protected $_destDir;
	
	/**
	 * Module's own log file to log import progress, and if something goes wrong
	 * @var string
	 */
	protected  $_logFile;
	
	/**
	 * The datahandler
	 * @var AMALexDataHandler
	 */
	protected  $_dh;
	
	/**
	 * true if the xml must validate with its dtd
	 */
	protected $_mustValidate;

    /**
     * name constructor
     */
    public function __construct() {
    	
    	$this->_mustValidate = false;
    	
    	/**
    	 * real uploaded filename must be in $_SESSION[UPLOAD_SESSION_VAR]['filename']
    	 * set by js/include/jquery/pekeUpload/upload.php
    	 */
    	if (isset($_SESSION[UPLOAD_SESSION_VAR]['filename']) &&
			strlen($_SESSION[UPLOAD_SESSION_VAR]['filename'])>0) {
    		$this->_importFileName = $_SESSION[UPLOAD_SESSION_VAR]['filename'];
    		unset ($_SESSION[UPLOAD_SESSION_VAR]);
    	}
    	
    	$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
    	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
    	$this->_dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));
    }

    /**
     * runs the import from the XML found in the uploaded zip file
     * 
     * @return number total count of imported items from all xml files
     */
	public function run() {
		$zip = new ZipArchive();
		$importedItems = 0; // counts total imported items from all xml files
		if ($zip->open($this->_importFileName)) {
			// flush and terminate output buffer
			ob_end_flush();
			$this->_logMessage(translateFN('Importazione iniziata').'...');
			$this->_logMessage('['.date('d/m/Y H:i:s').']');
			$time_start = microtime(true);
			$this->_logMessage(translateFN('Importo da').': '.basename($this->_importFileName));
			$this->_logMessage(translateFN('Scompatto il file...'));
			// a wildcard search is needed, must unzip the file
			$this->_destDir = dirname($this->_importFileName). DIRECTORY_SEPARATOR .
											str_ireplace('.zip', '', basename($this->_importFileName));
			$zip->extractTo($this->_destDir);
			$this->_logMessage('['.translateFN('OK').']');
			
			/**
			 * cannot use GlobIteartor object as in commented code because of
			 * this PhP bug: https://bugs.php.net/bug.php?id=55701
			 *
			 * unusable code is commented out, workaround is implemented
			 */
		
			// iterate all *.xml files found inside the uploaded zip
			
			// $filesIterator = new GlobIterator($this->_destDir . DIRECTORY_SEPARATOR . '*.xml');
			// if ($filesIterator->count()>0) {
				 
				$dom = new DOMDocument();
				libxml_use_internal_errors(true);
				 
				foreach (new GlobIterator($this->_destDir . DIRECTORY_SEPARATOR . '*.xml') as $filesIterator) {
					$htmlDTDMessage = '';
					// load the xml
					$dom->load($filesIterator->getPath(). DIRECTORY_SEPARATOR . $filesIterator->getFilename());
					
					$validate = ($this->_mustValidate) ? $dom->validate() : true;
					
					if ($validate) {
						// if the xml validates against its own dtd, do the import
						// htmlDTDError is not an error message
						$htmlDTDMessage .= $filesIterator->getFilename().' '.translateFN('è valido').' DTD: '.$dom->doctype->systemId;
						$this->_logMessage($htmlDTDMessage);
						// call the extending class own import method on XML root node
						$currentLoopImportedItems = $this->_importXMLRoot ($dom->documentElement, $dom->doctype->name);
						if ($currentLoopImportedItems==-1) {
							// an error has occoured, stop running since it would be useless
							$this->_logMessage('**'.translateFN('Questo errore è fatale, l\'importazione è stata interrotta.').'**');
							break;
						} else {
							$importedItems += $currentLoopImportedItems;
						}
						 
					} else {
						$htmlDTDMessage .= $filesIterator->getFilename().' '.translateFN('NON è valido').' DTD: '.$dmc->doctype->publicId;;
						$errors = libxml_get_errors();
						foreach ($errors as $error) {
							$htmlDTDMessage .= $error->message.' '.translateFN('a riga').': '.$error->line;
						}
						libxml_clear_errors();
						$this->_logMessage($htmlDTDMessage);
					}
				}
				libxml_use_internal_errors(false);
			
			if ($importedItems <=0) $this->_logMessage(translateFN('Nessun file XML trovato.'));
			// this is commented because of the bug described above
			// } else {
			// 	$this->_logMessage(translateFN('Nessun file XML trovato.'));
			// }
			$this->_logMessage(translateFN('Importazione terminata').'...');
			$this->_logMessage('['.date('d/m/Y H:i:s').']');
			$this->_logMessage(translateFN('Rimozione files'));
			unlink ($this->_importFileName);
			rrmdir ($this->_destDir);
			unset  ($_SESSION['uploadHelper']);
			$this->_logMessage(translateFN('Tempo impiegato').'(min.) ...');
			$this->_logMessage('['.((microtime(true) - $time_start)/60).']');
		}
		return $importedItems;
	}
	
	/**
	 * Sets the log file name
	 * 
	 * @param string $filename
	 */
	protected function _setLogFileName ($filename) {
		// make the module's own log dir if it's needed
		if (!is_dir(MODULES_LEX_LOGDIR)) mkdir (MODULES_LEX_LOGDIR, 0777, true);
		// set the log file name
		$this->_logFile = MODULES_LEX_LOGDIR . $filename;
	}
	
	/**
	 * logs a message in the log file defined in the logFile private property.
	 * and sends output to the iframe in the browser as well
	 *
	 * @param string $text the message to be logged
	 *
	 * @return unknown_type
	 *
	 * @access private
	 */
	protected function _logMessage ($text)
	{
		// the file must exists, otherwise logger won't log
		if (!is_file($this->_logFile)) touch ($this->_logFile);
		ADAFileLogger::log($text, $this->_logFile);
		sendToBrowser($text);
	}	
} // class ends here