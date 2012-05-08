<?php

class Model_Db_MyProfiler extends Zend_Db_Profiler
{
    /**
     * The original label for this profiler.
     * @var string
     */
    protected $_label = null;

    /**
     * The message envelope holding the profiling summary
     * @var Zend_Wildfire_Plugin_FirePhp_TableMessage
     */
    protected $_message = null;

    /**
     * The total time taken for all profiled queries.
     * @var float
     */
    protected $_totalElapsedTime = 0;

    /**
     * Constructor
     *
     * @param string $label OPTIONAL Label for the profiling info.
     * @return void
     */
    public function __construct($label = null)
    {
        $this->_label = $label;
        if(!$this->_label) {
            $this->_label = 'Model_Db_MyProfiler';
        }
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED) {
            return;
        }

        $profile = $this->getQueryProfile($queryId);

        $this->_totalElapsedTime += $profile->getElapsedSecs();
        $this->_message[] = array((string)round($profile->getElapsedSecs(),5),
                                      $profile->getQuery(),
                                      ($params=$profile->getQueryParams())?$params:null);
    }

    public function getMessages()
    {
        return $this->_message;
    }

    public function getTotalElapsedTime()
    {
        return $this->_totalElapsedTime;
    }
}
