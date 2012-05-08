<?php

class Api_IndexController extends Zend_Controller_Action
{
    
    public function indexAction()
    {
        $session = new Zend_Session_Namespace();
        if (isset($session->user)) {
            $this->_helper->redirector('index', 'compte', 'api');
        }
        
        $service = new Service_User();
        $this->view->formRegistration = new Form_User(array('action' => '/api/inscription'));
    }
    
}