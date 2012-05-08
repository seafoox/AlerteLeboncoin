<?php


class Form_AlertMail extends Zend_Form
{
    
    public function init()
    {
        
        $this->addElement('text', 'link', array(
            'label' => 'Votre recherche (obligatoire)',
            'description' => 'Copiez/collez ici l\'adresse leboncoin.fr après avoir effectué votre recherche',
            'required' => true,
            'size' => 30
        ));
        
        $this->addElement('text', 'email', array(
            'label' => 'E-Mail (obligatoire)',
            'required' => true,
            'description' => 'Vous recevrez les alertes à cette adresse. Un lien de confirmation vous sera envoyé.'
        ));
        
        $this->addElement('text', 'title', array(
            'label' => 'Titre',
            'required' => false,
            'description' => 'Vous pouvez donner un titre à votre recherche.'
        ));
        
        $this->addElement('submit', 'register', array(
            'label' => 'Créer l\'alerte'
        ));
        
        $getId = create_function('$decorator', 'return $decorator->getElement()->getId()."-element";');
        $this->setElementDecorators(array(
            'ViewHelper', 'label', 'Errors', array('description', array('class' => 'description')),
            array('HtmlTag', array('tag' => 'div', 'id' => array('callback' => $getId), 'class' => 'element'))
        ));
        $this->getElement('register')->setDecorators(array(
            'Tooltip', 'ViewHelper',
            array('HtmlTag', array('tag' => 'div', 'id' => array('callback' => $getId)))
        ));
        
        $this->setDecorators(array('FormElements', 'Form'));
    }
    
}