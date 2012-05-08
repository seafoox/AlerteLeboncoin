<?php


class Form_User extends Zend_Form
{
    /**
     * @var Model_User
     */
    protected $_user;
    
    public function init()
    {
        $this->addElement('hidden', 'form', array('value' => 'registration'));
        
        $this->addElement('text', 'email', array(
            'label' => 'E-Mail',
            'required' => true
        ));
        
        $this->addElement('password', 'password', array(
            'label' => 'Mot de passe',
            'required' => true,
            'validators' => array(array('StringLength', false, array(6, 255))),
            'description' => '6 caractères minimum'
        ));
        
        $this->addElement('password', 'confirm_password', array(
            'label' => 'Confirmer le mot de passe',
            'required' => true,
            'validators' => array(array('Identical', false, 'password'))
        ));
        
        $this->addElement('submit', 'register', array(
            'label' => 'Terminer mon inscription'
        ));
        
        if ($this->_user) {
            $this->populate($this->_user->toArray());
        }
    }
    
    /**
    * @param Model_User $user
    * @return Form_AlertMail
    */
    public function setUser($user)
    {
        $this->_user = $user;
        return $this;
    }
    
    /**
    * @return Model_User
    */
    public function getUser()
    {
        return $this->_user;
    }
}