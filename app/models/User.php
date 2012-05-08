<?php


class Model_User extends Zend_Db_Table_Row
{
    /**
    * @param int $id
    * @return Model_User
    */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
    * @return int
    */
    public function getId()
    {
        return $this->id;
    }
    
    
    /**
    * @param string $email
    * @return Model_User
    */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    
    /**
    * @return string
    */
    public function getEmail()
    {
        return $this->email;
    }
    
    
    /**
    * @param string $password
    * @return Model_User
    */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    /**
    * @return string
    */
    public function getPassword()
    {
        return $this->password;
    }
    
    
    /**
    * @param string $date_created
    * @return Model_User
    */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }
    
    /**
    * @return string
    */
    public function getDateCreated($asString = false)
    {
        return $this->date_created;
    }
    
    
    /**
    * @param string $validation_key
    * @return Model_User
    */
    public function setValidationKey($validation_key)
    {
        $this->validation_key = $validation_key;
        return $this;
    }
    
    /**
    * @return string
    */
    public function getValidationKey()
    {
        return $this->validation_key;
    }
    
    /**
    * @param int $default_check_interval
    * @return Model_User
    */
    public function setDefaultCheckInterval($default_check_interval)
    {
        $this->default_check_interval = $default_check_interval;
        return $this;
    }
    
    /**
    * @return int
    */
    public function getDefaultCheckInterval()
    {
        return $this->default_check_interval;
    }

    
    
    public function save()
    {
        $clearPassword = $this->getPassword();
        if (!$this->getId()) {
            $this->setPassword(sha1($this->getPassword()));
        }
        
        $return = parent::save();
        $this->setPassword($clearPassword);
        return $return;
    }
    
    
}