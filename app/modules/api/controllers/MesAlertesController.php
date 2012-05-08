<?php


class Api_MesAlertesController extends Zend_Controller_Action
{
    public function configAction()
    {
        $table = new Model_DbTable_AlertMail();
        $alert = $table->fetchRow(array(
            'user_id = ?' => Zend_Auth::getInstance()->getStorage()->read()->getId(),
            'id = ?' => $this->_request->getParam('id')
        ));
        if (!$alert) {
            $this->_helper->redirector('mes-alertes', 'compte', 'api');
        }
        $form = new Form_AlertConfig();
        $values = $alert->toArray();
        if ($values['price_min'] <= 0) {
            unset($values['price_min']);
        }
        if ($values['price_max'] >= 1000000000) {
            unset($values['price_max']);
        }
        $form->populate($values);
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $alert->title = $form->getValue('title');
            $price = (int)$form->getValue('price_min');
            if (!$price || $price < 0) {
                $price = 0;
            }
            $alert->price_min = $price;
            $price = (int)$form->getValue('price_max');
            if (!$price || $price < 0 || $price < $alert->price_min) {
                $price = 1000000000;
            }
            $alert->price_max = $price;
            if ($cities = $form->getValue('cities')) {
                $alert->cities = implode("\n", array_map("trim", explode("\n", $form->getValue('cities'))));
            } else {
                $alert->cities = null;
            }
            $alert->check_interval = $form->getValue('check_interval');
            try {
                $alert->save();
                $this->_helper->redirector('config', 'mes-alertes', 'api', array(
                    'id' => $alert->id, 'success' => 1
                ));
            } catch (Zend_Db_Exception $e) {
                $form->addError('Une erreur inconnue est survenue.');
            }
        }
        $this->view->form = $form;
        $this->view->alert = $alert;
        $this->view->success = $this->_request->getParam('success') == 1;
    }

    public function supprimerAction()
    {
        $table = new Model_DbTable_AlertMail();
        $alert = $table->fetchRow(array(
        	'user_id = ?' => Zend_Auth::getInstance()->getStorage()->read()->getId(),
            'id = ?' => $this->_request->getParam('id')
        ));
        if (!$alert) {
            $this->_helper->redirector('mes-alertes', 'compte', 'api');
        }
        
        $this->view->alert = $alert;
        
        if (!$this->_request->isPost()) {
            return;
        }
        
        $alert->delete();
        
        $this->_helper->redirector('mes-alertes', 'compte', 'api');
    }
    
    
    public function suspendreAction()
    {
        $service = new Service_LeBonCoin();
        $service->pauseAlertMail($this->_request->getParam('id'), Zend_Auth::getInstance()->getStorage()->read());
        
        $this->_helper->redirector('mes-alertes', 'compte', 'api');
    }
    
    
    public function reprendreAction()
    {
        $service = new Service_LeBonCoin();
        $service->resumeAlertMail($this->_request->getParam('id'), Zend_Auth::getInstance()->getStorage()->read());
        
        $this->_helper->redirector('mes-alertes', 'compte', 'api');
    }
    
    
    public function changerIntervalleAction()
    {
        if ($this->_request->isPost()) {
            $service = new Service_LeBonCoin();
            $service->changeIntervalAlertMail(
                Zend_Auth::getInstance()->getStorage()->read(),
                $this->_request->getParam('id'),$this->_request->getPost('interval')
            );
        }
        
        $this->_helper->redirector('mes-alertes', 'compte', 'api');
    }
    
    
    public function changerTitreAction()
    {
        $table = new Model_DbTable_AlertMail();
        $alert = $table->fetchRow(array(
        	'user_id = ?' => Zend_Auth::getInstance()->getStorage()->read()->getId(),
            'id = ?' => $this->_request->getParam('id')
        ));
        if (!$alert) {
            $this->_helper->redirector('mes-alertes', 'compte', 'api');
        }
        
        $form = new Form_Alert_Title();
        $form->getElement('title')->setValue($alert->title);
        
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $alert->title = $form->getValue('title');
            $alert->save();
            $this->_helper->redirector('mes-alertes', 'compte', 'api');
        }
        
        $this->view->alert = $alert;
        $this->view->form = $form;
    }
}











