<?php


class Model_Mail extends Zend_Mail
{
    public function __construct($charset = 'utf-8')
    {
        parent::__construct($charset);
        $config = Zend_Registry::get("config");
        if ($config->email && $config->email->from) {
            $this->setFrom($config->email->from);
        }
    }
}