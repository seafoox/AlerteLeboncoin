<?php


class Form_AlertMailUser extends Form_AlertMail
{
    
    public function init()
    {
        parent::init();
        $this->addAttribs(array('class' => 'alert-mail'));
        $this->removeElement('email');
        $this->removeElement('title');
        $this->getElement('link')
            ->setLabel('Ajouter une alerte (lien recherche leboncoin) :')
            ->setAttrib('class', 'link-alert')
            ->setAttrib('size', 65);
        
        $this->addDecorators(array(
            'FormElements', array('HtmlTag', array('tag' => 'div')), 'Form'
        ));
        $this->setElementDecorators(array('ViewHelper', 'Label', 'Errors'), array('link'));
        $this->setElementDecorators(array('ViewHelper', 'Tooltip'), array('register'));
    }
    
}