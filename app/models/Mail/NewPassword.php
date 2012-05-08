<?php


class Model_Mail_NewPassword extends Model_Mail
{
    /**
     * @var Model_User
     */
    protected $_user;
    
    protected $_password;
    
    
    /**
    * @param Model_User $user
    * @return Model_Mail_Registration
    */
    public function setUser(Model_User $user)
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
    
    
    /**
    * @param string $password
    * @return Model_Mail_NewPassword
    */
    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }
    
    /**
    * @return string
    */
    public function getPassword()
    {
        return $this->_password;
    }
    
    
    public function send($transport = null)
    {
        $this->setSubject('Votre nouveau mot de passe');
        $this->addTo($this->_user->getEmail());
        
        $this->setBodyText('Bonjour,
Un nouveau mot de passe vous a été attribué. Vous pouvez à tout moment le changer en vous connectant à votre compte.

Login : '.$this->_user->getEmail().'
Mot de passe : '.$this->getPassword().'

http://'.$_SERVER["HTTP_HOST"].'/api/compte

Alerte LeBonCoin');
        
        return parent::send($transport);
    }
}
