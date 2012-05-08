<?php

class Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

	/**
	 * @var Zend_Auth instance
	 */
	protected $_auth;

	protected $_rules = array(
		'api' => array(
	        'compte' => array('index', 'mes-alertes'),
            'csv' => array(),
            'mes-alertes' => array()
	    )
	);

	public function __construct()
	{
		$this->_auth = Zend_Auth::getInstance();
	}

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();

		$view = Zend_Layout::getMvcInstance()->getView();

		if ($this->_auth->hasIdentity()) {
		    $view->user = $this->_auth->getStorage()->read();

		    $view->user->setTable(new Model_DbTable_User());
            try {
                $view->user->refresh();
                return;
		    } catch (Zend_Db_Table_Row_Exception $e) {
                unset($view->user);
            }
		}

		if (isset($this->_rules[$module])) {
		    Zend_Layout::getMvcInstance()->setLayout('api');
			if (!$this->_rules[$module]) {
				$request->setModuleName('api')
					->setControllerName('compte')
					->setActionName('auth');
			} elseif (isset($this->_rules[$module][$controller])) {
				if (!$this->_rules[$module][$controller] || in_array($action, $this->_rules[$module][$controller])) {
					$request->setModuleName('api')
						->setControllerName('compte')
						->setActionName('auth');
				}
			}
		}
	}
}




