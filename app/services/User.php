<?php

class Service_User
{
    /**
     * @var Model_DbTable_User
     */
    protected $_table;

    /**
     * @return Model_DbTable_User
     */
    public function getTable()
    {
        if (!isset($this->_table)) {
            $this->_table = new Model_DbTable_User();
        }
        return $this->_table;
    }

    /**
     * @return Model_User
     */
    public function createUser(array $options = array())
    {
        $table = new Model_DbTable_User();
        $user = $table->createRow();

        $date = new Zend_Date();
        $user->setDateCreated($date->get(Zend_Date::ISO_8601))
            ->setFromArray($options);

        $key = '';
        $chaine = 'abcdefghijklmnpqrstuvwxy';
        $lenght = strlen($chaine);
        srand((double)microtime()*1000);
        for($i=0; $i < 20; $i++) {
            $key .= $chaine[rand() % $lenght];
        }
        $user->setValidationKey(sha1($key));
        return $user;
    }

    /**
     * Génération d'un nouveau mot de passe
     * @param Model_User $user
     * @return string
     */
    public function generatePassword(Model_User $user)
    {
        $password = '';
        $chaine = 'abcdefghijklmnpqrstuvwxy0123456789';
        $lenght = strlen($chaine);
        srand((double)microtime()*1000);
        for($i=0; $i < 7; $i++) {
            $password .= $chaine[rand() % $lenght];
        }
        $user->password = sha1($password);
        return $password;
    }

    /**
     * @param string_type $email
     * @param string $password
     * @param bool $remember_me
     * @return boolean
     */
    public function auth($email, $password, $remember_me = false)
    {
        $password = sha1($password);
        $table = new Model_DbTable_User();
        $user = $table->fetchRow(array(
            'email = ?' => $email,
            'password = ?' => $password
        ));
        if ($user !== null && !$user->getValidationKey()) {
            if ($remember_me) {
                Zend_Session::rememberMe(365*24*3600);
                $saveHandler = Zend_Session::getSaveHandler();
                $saveHandler->setLifetime(365*24*3600)
                    ->setOverrideLifetime(true);
            }
            Zend_Auth::getInstance()->getStorage()->write($user);
        }
        return $user;
    }

    /**
     * @param string $key
     * @return Model_User
     */
    public function validate($key)
    {
        $table = new Model_DbTable_User();
        $user = $table->fetchRow(array('validation_key = ?' => $key));
        if (null !== $user) {
            $user->setValidationKey(null)->save();
            $tableAlerte = new Model_DbTable_AlertMail();
            $tableAlerte->update(
                array('user_id' => $user->getId(), 'validated' => 1),
                array('email = ?' => $user->getEmail())
            );
        }
        return $user;
    }

    /**
     * @param Model_User $user
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAlerts(Model_User $user)
    {
        $table = new Model_DbTable_AlertMail();
        return $table->fetchAll(array('user_id = ?' => $user->getId()));
    }
}
