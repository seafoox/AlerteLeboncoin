<?php


class Form_LostPassword extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'email', array(
            'label' => 'E-Mail'
        ));
        
        $this->addElement('submit', 'sendme', array(
            'label' => 'Valider'
        ));
    }
}