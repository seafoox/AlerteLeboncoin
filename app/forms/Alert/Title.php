<?php


class Form_Alert_Title extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'title', array(
            'size' => 80,
            'required' => true
        ));
        
        $this->addElement('submit', 'change', array(
            'label' => 'Sauvegarder'
        ));
    }
}