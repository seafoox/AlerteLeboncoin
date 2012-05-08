<?php

class Api_InscriptionController extends Zend_Controller_Action
{
    
    public function validerAction()
    {
        $service = new Service_User();
        if (null !== $user = $service->validate($this->_request->getParam('key'))) {
            Zend_Auth::getInstance()->getStorage()->write($user);
            $this->_helper->redirector('mes-alertes', 'compte', 'api');
        }
    }
    
    public function termineeAction()
    {
        
    }
}