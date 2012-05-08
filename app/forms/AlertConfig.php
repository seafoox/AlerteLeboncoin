<?php

class Form_AlertConfig extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('id', 'morealertconfig');
        $this->addElement('text', 'title', array(
            'label' => 'Titre de votre alerte',
            'description' => 'Permet de retrouver plus facilement votre alerte.',
            'required' => true,
            'size' => 50
        ));

        $this->addElement('select', 'check_interval', array(
            'label' => 'M\'envoyer une alerte toutes les',
            'description' => 'Une alerte vous sera envoyée seulement si il existe une nouvelle annonce.',
            'required' => true,
            'multiOptions' => array(
                15 => '15 mins', 30 => '30 mins', 60 => '1 heure',
                120 => '2 heures', 720 => '12 heures', 1440 => '24 heures'
            )
        ));

        $this->addElement('text', 'price_min', array(
            'label' => 'Prix min',
            'size' => 5
        ));

        $this->addElement('text', 'price_max', array(
            'label' => 'Prix max',
            'size' => 5
        ));
        $this->addElement('textarea', 'cities', array(
            'label' => 'Villes (une par ligne)',
            'description' => '<strong style="color: red;">Écrivez les villes telle qu\'elles sont affichées sur Leboncoin (utilisez le bon vieux copier/coller).</strong>',
            'size' => 50, 'cols' => 50, 'rows' => 8
        ));

        $this->addDisplayGroup(array('title', 'check_interval'), 'base', array(
            'legend' => 'Information de base'
        ));
        $this->addDisplayGroup(array('price_min', 'price_max', 'cities'), 'more_filter', array(
            'legend' => 'Filtres supplémentaire'
        ));

        $this->addElement('submit', 'register', array(
            'label' => 'Enregistrer'
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
        $this->getElement('cities')->getDecorator('description')->setOption('escape', false);
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setDisplayGroupDecorators(array(
            'FormElements', 'Fieldset'
        ));
    }
}
