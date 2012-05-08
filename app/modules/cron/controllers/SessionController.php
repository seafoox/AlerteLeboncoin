<?php

class Cron_SessionController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender();
    }

    public function purgeAction()
    {
        $tb = new Zend_Db_Table("Session");
        $tb->delete(array("(UNIX_TIMESTAMP() - modified) > lifetime" => true));
    }
}
