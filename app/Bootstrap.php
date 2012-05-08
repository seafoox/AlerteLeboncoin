<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function run()
    {
        Zend_Registry::set("config", new Zend_Config($this->getOptions()));
        parent::run();
    }

    /**
     * @return Zend_Application_Module_Autoloader
     */
    public function _initAutoloader()
    {
        return new Zend_Application_Module_Autoloader(array(
            "namespace" => "", "basePath"  => APPLICATION_PATH
        ));
    }

    public function _initPlugins()
    {
        $this->bootstrap("autoloader");
        Zend_Controller_Front::getInstance()
            ->registerPlugin(new Plugin_Setup())
            ->registerPlugin(new Plugin_Auth());
    }

    public function _initMail()
    {
        $options = $this->getOption("email");
        if (isset($options["from"])) {
            Zend_Mail::setDefaultTransport(
                new Zend_Mail_Transport_Sendmail("-f".$options["from"])
            );
        }
    }

    /**
     * @return Zend_View
     */
    public function _initView()
    {
        $view = new Zend_View();
        $view->doctype("XHTML1_STRICT");
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            "ViewRenderer"
        );
        $viewRenderer->setView($view);
        $view->addBasePath(APPLICATION_PATH."/modules/default/views");
        return $view;
    }

    /**
     * @return Zend_Locale
     */
    public function _initLocale()
    {
        $this->bootstrap("cache");
        $this->bootstrap("frontController");
        if (null !== $cache = $this->getResource("cache")) {
            Zend_Locale::setCache($cache);
        }
        $locale = new Zend_Locale("fr_FR");
        Zend_Registry::set("Zend_Locale", $locale);
        return $locale;
    }

    /**
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    protected function _initCache()
    {
        $this->bootstrap("autoloader");
        $cache = Zend_Cache::factory("Core", "File",
            array("automatic_serialization" => true),
            array("cache_dir" => realpath(APPLICATION_PATH."/../var/tmp"))
        );
        $cache->clean(Zend_Cache::CLEANING_MODE_OLD);
        Zend_Registry::set("Zend_Cache", $cache);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        return $cache;
    }

    /**
     * @return Zend_Cache_Manager
     */
    public function _initCacheManager()
    {
        // cache manager
        $manager = new Zend_Cache_Manager();
        $manager->setCacheTemplate("feed", array(
            "frontend" => array(
                "name" => "Core",
                "options" => array(
                    "lifetime" => 1200,
                    "automatic_serialization" => true
                )
            ),
            "backend" => array(
                "name" => "File",
                "options" => array(
                    "cache_dir" => realpath(APPLICATION_PATH."/../var/tmp"),
                    "file_name_prefix" => "zend_cache_feed"
                )
            )
        ));
        return $manager;
    }

    /**
     * @return Zend_Log
     */
    public function _initLog()
    {
        $logger = new Zend_Log();
        try {
            $writer = new Zend_Log_Writer_Stream(
                realpath(APPLICATION_PATH."/../var/log")."/error.log");
            $logger->addWriter($writer);
        } catch (Zend_Log_Exception $e) {
            // erreur ? Pas de bol …
        }
        Zend_Registry::set("logger", $logger);
        return $logger;
    }

    public function _initTranslator()
    {
        $translator = new Zend_Translate(array(
            "adapter" => "array",
            "content" => APPLICATION_PATH."/../langs",
            "locale"  => "fr",
            "scan" => Zend_Translate::LOCALE_DIRECTORY
        ));
        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }

    public function _initSession()
    {
        $this->bootstrap("db");
//         $adapter = Zend_Db_Table::getDefaultAdapter();
//         if (!$adapter->getConnection()) {
//             return;
//         }
        Zend_Session::setSaveHandler(
            new Zend_Session_SaveHandler_DbTable(array(
                "name" => "Session",
                "primary" => "id",
                "modifiedColumn" => "modified",
                "dataColumn" => "data",
                "lifetimeColumn" => "lifetime"
            ))
        );
        Zend_Session::start(array(
            "cookie_lifetime" => 0,
            "use_only_cookies" => 1,
            "hash_function" => 1,
            "cookie_httponly" => 1
        ));
    }
}

