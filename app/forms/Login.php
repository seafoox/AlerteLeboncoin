<?php

class Form_Login extends Zend_Form
{
    public function init()
    {
        $this->addElement('hidden', 'form', array('value' => 'login'));

        $this->addElement('text', 'email', array(
            'label' => 'E-Mail'
        ));

        $this->addElement('password', 'password', array(
            'label' => 'Mot de passe'
        ));

        $this->addElement('radio', 'remember_me', array(
            'label' => 'Connexion permanente',
            'multiOptions' => array(1 => 'oui', 0 => 'non'),
            'separator' => ' ',
            'value' => 0
        ));

        $this->addElement('submit', 'connection', array(
            'label' => 'Connexion'
        ));
    }
}
