<?php

class Api_CompteController extends Zend_Controller_Action
{
    public function authAction()
    {
		$formLogin = new Form_Login();
        $formRegistration = new Form_User();

		if ($this->_request->isPost()) {
            $service = new Service_User();
		    if ($this->_request->getPost('form') == 'login' && $formLogin->isValid($this->_request->getPost())) {
                if (null !== $user = $service->auth($formLogin->getValue('email'), $formLogin->getValue('password'), (bool)$formLogin->getValue('remember_me'))) {
                    if (null === $user->getValidationKey()) {
                        $this->_helper->redirector->gotoUrlAndExit("http://".$_SERVER['HTTP_HOST']."/".ltrim($_SERVER['REQUEST_URI'], "/"));
                    } else {
                        $formLogin->getElement('password')->addError('Votre compte n\'a pas été validé.');
                    }
                } else {
                    $formLogin->getElement('password')->addError('E-Mail ou mot de passe incorrecte.');
                }
		    } elseif ($this->_request->getPost('form') == 'registration' && $formRegistration->isValid($this->_request->getPost())) {
                $user = $service->createUser($formRegistration->getValues());
                try {
                    $user->save();
                    $mail = new Model_Mail_Registration();
                    $mail->setUser($user)->send();
                    $this->_helper->redirector('terminee', 'inscription', 'api');
                } catch (Zend_Db_Exception $e) {
                    if (preg_match('/Duplicate entry \'[^\']*\' for key \'email\'/', $e->getMessage())) {
                        $formRegistration->getElement('email')->addError('Cette adresse E-Mail est déjà utilisée.');
                    }
                }
		    }
		}
        $this->view->formInscription = $formRegistration;
        $this->view->formLogin = $formLogin;
    }

    public function indexAction()
    {
        $formPassword = new Form_User_Password();
        $formSettings = new Form_User_Settings();

        /* @var $user Model_User */
        $user = Zend_Auth::getInstance()->getStorage()->read();
        $user->setTable(new Model_DbTable_User());
        $formSettings->getElement('interval')->setValue($user->getDefaultCheckInterval());

        if ($this->_request->isPost()) {
            if (($this->_request->getPost('form') == 'password') && $formPassword->isValid($this->_request->getPost())) {
                $user->setPassword(sha1($formPassword->getValue('password')))->save();
                $this->_helper->redirector('index', 'compte', 'api');
            } elseif (($this->_request->getPost('form') == 'settings') && $formSettings->isValid($this->_request->getPost())) {
                $user->setDefaultCheckInterval($formSettings->getValue('interval'))->save();
                $this->_helper->redirector('index', 'compte', 'api');
            }
        }
        $this->view->formPassword = $formPassword;
        $this->view->formSettings = $formSettings;
    }

    public function deconnexionAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'index', 'default');
    }

    public function motDePasseOublieAction()
    {
        $form = new Form_LostPassword();
        $this->view->validate = false;
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $serviceUser = new Service_User();
            $user = $serviceUser->getTable()->fetchRow(array('email = ?' => $form->getValue('email')));
            if (null !== $user) {
                $password = $serviceUser->generatePassword($user);
                $user->save();
                $mail = new Model_Mail_NewPassword();
                $mail->setUser($user)->setPassword($password);
                $mail->send();
                $this->view->validate = true;
            } else {
                $form->getElement('email')->addError('Tiens, je n\'ai pas trouvé de compte pour cette adresse.');
            }
        }
        $this->view->form = $form;
    }

    public function mesAlertesAction()
    {
        $serviceUser = new Service_User();
        $form = new Form_AlertMailUser();
        if ($link = $this->_request->getParam('prelink')) {
            $form->populate(array('link' => $link));
            $this->view->link = $link;
        }
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $service = new Service_LeBonCoin();
            try {
                $service->checkUri($this->_request->getParam('link'));
                $alert = $service->addAlertMail($form->getValue('email'), $form->getValue('link'), Zend_Auth::getInstance()->getStorage()->read());
                $this->_helper->redirector('changer-titre', 'mes-alertes', 'api', array('id' => $alert->id));
            } catch (Zend_Db_Exception $e) {
                if (false !== strpos($e->getMessage(), 'Duplicate entry')) {
                    $form->getElement('link')->addError('Une alerte sur cette recherche existe déjà.');
                } else {
                    throw new Exception($e);
                }
            } catch (Exception $e) {
                if (Zend_Registry::isRegistered("logger")) {
                    $msg = $e->getMessage();
                    if (false === strpos($msg, ":")) {
                        $msg .= " : ".$form->getValue('link');
                    }
                    Zend_Registry::get("logger")->warn("Adresse est invalide: ".
                        __FILE__.' - Ligne: '.__LINE__.' - Erreur: '.
                        $msg);
                }
                $form->getElement('link')->addError('Cette adresse est invalide');
            }
        }

        $this->view->form = $form;
        $this->view->alerts = $serviceUser->fetchAlerts(Zend_Auth::getInstance()->getStorage()->read());
    }
}
