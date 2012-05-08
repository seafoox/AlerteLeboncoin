<?php

class Plugin_Setup extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout($request->getModuleName());
		
		$view = $layout->getView();
		
		$view->currentModule = $request->getModuleName();
		$view->currentController = $request->getControllerName();
		$view->currentAction = $request->getActionName();
	}

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$view = Zend_Layout::getMvcInstance()->getView();
		$view->requestModule = $request->getModuleName();
		$view->requestController = $request->getControllerName();
		$view->requestAction = $request->getActionName();
	}

	public function dispatchLoopShutdown()
	{
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $profiler = $adapter->getProfiler();
        if ($profiler->getEnabled()) {
            $logger = new Zend_Log();
            try {
                $writer = new Zend_Log_Writer_Stream(realpath(APPLICATION_PATH.'/../var/log').'/profiler-db.log');
                $logger->addWriter($writer);
            } catch (Zend_Log_Exception $e) {
                return;
            }
            $logger->info("-====================================================================================-");
            $logger->info($profiler->getTotalNumQueries()." @ ".(string)round($profiler->getTotalElapsedTime(),5)." sec");
            if ($messages = $profiler->getMessages()) {
                foreach ($profiler->getMessages() AS $msg) {
                    $logger->info($msg[0]." | ".$msg[1]." | ".Zend_Json::encode($msg[2]));
                }
            }
        }
	}
}