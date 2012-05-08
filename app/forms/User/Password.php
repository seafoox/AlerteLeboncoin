<?php


class Form_User_Password extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('autocomplete', 'off');
        $this->addElement('hidden', 'form', array(
            'value' => 'password'
        ));
        
        $this->addElement('password', 'password', array(
            'label' => 'Nouveau mot de passe',
            'required' => true,
            'validators' => array(array('StringLength', false, array(6, 255))),
            'description' => '6 caractères minimum'
        ));
        
        $this->addElement('password', 'confirm_password', array(
            'label' => 'Confirmer le mot de passe',
            'required' => true,
            'validators' => array(array('Identical', false, 'password'))
        ));
        
        $this->addElement('submit', 'change', array(
            'label' => 'Modifier mon mot de passe'
        ));
    }
}