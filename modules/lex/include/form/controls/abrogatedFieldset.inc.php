<?php
/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           lex
 * @version		   0.1
 */

class abrogatedFieldset extends FCFieldset {
	
	private $_fields;
	private $_rows = array();
	
	public function __construct($abrogatedRows) {
		
		parent::__construct(null, null, null);
		
		$this->_fields = array ( 
							'abrogato_da'=> translateFN('Abrogato Da').' (ID)',
							'data_abrogazione'=>translateFN('Data Abrogazione'),
							'azioni'=>translateFN('Azioni')				
						);
		
		$count = count($abrogatedRows);
		/**
		 * Place as many rows as passed array elements
		 */
		if ($count>0) {
			foreach ($abrogatedRows as $i=>$abrogatedRow) {
				foreach (array_keys($this->_fields) as $j=>$field) {
					
					if ($j==0) {
						// first field is string and has a label
						$validator = FormValidator::NOT_EMPTY_STRING_VALIDATOR;
						$label = translateFN('ID Asset').': ';
					} else if ($j==1) {
						// second field is a date and has no label
						$validator = FormValidator::DATE_VALIDATOR;
						$label = translateFN('Data').': ';
					}
					
					if ($j!=count($this->_fields)-1) {
						// if $j it's not the last field, that is actions
						$this->_rows[$i][$field] = FormControl::create(FormControl::INPUT_TEXT, $field.'_'.$i,$label)
						->withData($abrogatedRow[$field])
						->setRequired()
						->setValidator($validator);
						
						if ($validator==FormValidator::DATE_VALIDATOR) {
							// add a classe for jQuery to build the datepicker
							$this->_rows[$i][$field]->setAttribute('class','datepicker');
						}
						
					} else {
						
						$type = 'delete';
						$title = translateFN ('Clicca per cancellare l\'abrogazione');
						$link = 'deleteAbrogation ($j(this), '.$abrogatedRow['abrogato_da'].' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
						
						$button = FormControl::create(FormControl::INPUT_BUTTON, 'delete_'.$abrogatedRow['abrogato_da'], '');
						$button->setAttribute('onclick', 'javascript:'.$link);
						$button->setAttribute('class', $type.'Button tooltip');
						$button->setAttribute('title',$title);
						
						$this->_rows[$i][$field] = $button;
					}
					
				}
			}
			$count = ++$i;
		}
		/**
		 * and one empty row
		 */
		foreach (array_keys($this->_fields) as $j=>$field) {
			if ($j==0) {
				// first field is string and has a label
				$validator = FormValidator::NOT_EMPTY_STRING_VALIDATOR;
				$label = translateFN('ID Asset').': ';
			} else if ($j==1) {
				// second field is a date and has no label
				$validator = FormValidator::DATE_VALIDATOR;
				$label = translateFN('Data').': ';
			}
			if ($j!=count($this->_fields)-1) {
				// if $j it's not the last field, that is actions
				$this->_rows[$count][$field] = FormControl::create(FormControl::INPUT_TEXT, $field.'_'.$count,$label)
				->setValidator($validator);
				
				if ($validator==FormValidator::DATE_VALIDATOR) {
					// add a classe for jQuery to build the datepicker
					$this->_rows[$count][$field]->setAttribute('class','datepicker');
				}
				
			} else {
				// last row has no delete button, add a null control
				$this->_rows[$count][$field] = new FCNullControl(null, null, null);
			}
		}
	}
	
	public function render() {
		$tableData = array();
		// render every element
		foreach ($this->_rows as $i=>$row) {
			foreach (array_keys($this->_fields) as $field) {
				if (!$row[$field] instanceof FCNullControl) {
					$tableData[$i][$field] = $row[$field]->render();
				} else {
					$tableData[$i][$field] = '&nbsp;';
				}
			}			
		}
		
		// add row link as table footer
		
		$footer = CDOMElement::create('a','onclick:javascript:addAbrogatedRow();');
		$footer->addChild(new CText('&#43; '.translateFN('Clicca per aggiungere una riga')));
		
		$tfooter = array ($footer->getHtml(), '&nbsp;', '&nbsp;');
		
		// put them into a table and return
		$table = BaseHtmlLib::tableElement('id:formAbrogatedTable',$this->_fields,$tableData, $tfooter);		
		return $table->getHtml();
	}
	
	
	public function getControls() {
		$controls = array();
		foreach ($this->_rows as $row) {
			foreach (array_keys($this->_fields) as $field) {
				$controls[] = $row[$field];
			}
		}
		return $controls;
	}
}