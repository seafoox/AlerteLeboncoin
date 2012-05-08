<?php

class Zend_View_Helper_Date extends Zend_View_Helper_Abstract
{
	
	/**
	 * @var Zend_Date
	 */
	protected $_date;
	
	public function __construct()
	{
		if ($this->_date === null) {
			$this->_date = new Zend_Date();
		}
	}
	
	public function date($date = null)
	{
		if ($date === null) {
			return $this->_date;
		}
		
		$this->_date->set($date);
		
		return $this->_date;
	}
	
	public function getDate()
	{
		return $this->_date->get(Zend_Date::DATE_MEDIUM);
	}
	
	
	public function __call($function, $args)
	{
		return call_user_func_array(array($this->_date, $function), $args);
	}
	
}