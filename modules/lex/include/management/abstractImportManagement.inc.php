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
	protected $_importFileName;
	protected $_destDir;
	/**
	 * Module's own log file to log import progress, and if something goes wrong
	 * @var string
	 */
	protected  $_logFile;
	
	/**
	 * the datahandler
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
    	 * real uploaded filename must be in $_SESSION['uploadHelper']['filename']
    	 * set by js/include/jquery/pekeUpload/upload.php
    	 */
    	if (isset($_SESSION['uploadHelper']['filename']) &&
			strlen($_SESSION['uploadHelper']['filename'])>0) 
    		$this->_importFileName = $_SESSION['uploadHelper']['filename'];  
    	
    	$this->_dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
    }
    
	public function run() {
		$zip = new ZipArchive();
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
		
			// iterate all *.xml files found inside the uploaded zip
			$filesIterator = new GlobIterator($this->_destDir . DIRECTORY_SEPARATOR . '*.xml');
		
			if ($filesIterator->count()>0) {
				 
				$dom = new DOMDocument();
				libxml_use_internal_errors(true);
				 
				foreach ($filesIterator as $file) {
					$htmlDTDMessage = '';
					// load the xml
					$dom->load($filesIterator->getPath(). DIRECTORY_SEPARATOR . $filesIterator->getFilename());
					
					$validate = ($this->_mustValidate) ? $dom->validate() : true;
					
					if ($validate) {
						// if the xml validates against its own dtd, do the import
						// htmlDTDError is not an error message
						$htmlDTDMessage .= $filesIterator->getFilename().' '.translateFN('è valido').' DTD: '.$dom->doctype->systemId;
						$this->_logMessage($htmlDTDMessage);
						$this->_importXMLRoot ($dom->documentElement, $dom->doctype->name);
					}
					else {
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
			} else {
				$this->_logMessage(translateFN('Nessun file XML trovato.'));
			}
			$this->_logMessage(translateFN('Importazione terminata').'...');
			$this->_logMessage('['.date('d/m/Y H:i:s').']');
			$this->_logMessage(translateFN('Rimozione files'));
			unlink ($this->_importFileName);
			rrmdir ($this->_destDir);
			unset  ($_SESSION['uploadHelper']);
			$this->_logMessage(translateFN('Tempo impiegato').'(min.) ...');
			$this->_logMessage('['.((microtime(true) - $time_start)/60).']');
		}
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