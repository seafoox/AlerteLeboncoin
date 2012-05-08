<?php


class AlerteMailController extends Zend_Controller_Action
{
    public function index()
    {
        
    }
    
    
    public function creerAction()
    {
        $form = new Form_AlertMail();
        if ($link = $this->_request->getParam('link')) {
            $form->populate(array('link' => $link));
            $this->view->link = $link;
        }
        
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $service = new Service_LeBonCoin();
            try {
                $service->checkUri($this->_request->getParam('link'));
                $service->addAlertMail($form->getValues(), null);
                $this->_helper->redirector('creer', 'alerte-mail', 'default', array('terminee' => 1));
            } catch (Zend_Db_Exception $e) {
                if (false !== strpos($e->getMessage(), 'Duplicate entry')) {
                    $form->getElement('link')->addError('Une alerte sur cette recherche existe déjà.');
                } else {
                    throw new Exception($e);
                }
            } catch (Exception $e) {
                $this->view->error = 'Cette adresse de recherche est invalide';
            }
        }
        $this->view->form = $form;
        $this->view->valide = $this->_request->getParam('terminee');
    }

    public function validerAction()
    {
        $key = $this->_request->getParam('key');
        if ($key) {
            $service = new Service_LeBonCoin();
            if ($alert = $service->validateAlertMail($key)) {
                $this->view->alertMail = $alert;
            }
        }
    }

    public function supprimerAction()
    {
        $key = $this->_request->getParam('key');
        if ($key) {
            $service = new Service_LeBonCoin();
            if ($alert = $service->findAlertMailByKey($key)) {
                $this->view->alertMail = $alert;
                $service->deleteAlertMail($key);
            }
        }
    }
}
