<?php


class Form_User_Settings extends Zend_Form
{
    public function init()
    {
        $this->addElement('hidden', 'form', array(
            'value' => 'settings'
        ));
        
        $this->addElement('select', 'interval', array(
            'label' => 'Intervalle entre chaque vérification',
            'required' => true,
            'multiOptions' => array(
                15 => '15 mins', 30 => '30 mins',
                60 => '1 heure', 120 => '2 heures',
                720 => '12 heures', 1440 => '24 heures'
            ),
            'description' => 'Durée par défaut entre chaque vérification de nouvelle annonce.'
        ));
        
        $this->addElement('submit', 'change', array(
            'label' => 'Sauvegarder'
        ));
    }
}
