<?php
set_time_limit(0);

class Cron_AlertMailController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->disableLayout(true);
		$this->_helper->viewRenderer->setNoRender();
    }

    public function sendAction()
    {
        if ($this->_request->getParam('key') != Zend_Registry::get("config")->key) {
            return;
        }

        $service = new Service_LeBonCoin();
        $table = new Zend_Db_Table('AlertMail');
        $date = new Zend_Date();

        $where = array(
        	'validated = ?' => true, // alerte validée ?
        	'stop = ?' => false, // alert active ?
        	'(date_updated_check + INTERVAL (check_interval-1) MINUTE) <= NOW()' => true // dernier contrôle il y a plus de XX minutes
        );
        $process = explode('-', $this->_request->getParam('process'));
        $process = array_filter($process, 'is_numeric');
        if ($process) {
            $where["process IN (?)"] = $process; // permet de tâche en parallèle
        }
        $interval = explode('-', $this->_request->getParam('interval'));
        $interval = array_filter($interval, 'is_numeric');
        if ($interval) {
            $where["check_interval IN (?)"] = $interval; // par intervalle
        }

        $alerts = $table->fetchAll($where, array('date_updated_check ASC'));
        
        $dateNow = new Zend_Date();
        foreach ($alerts AS $alert) {
            try {
                $alert->link = str_replace(" ", "%20", $alert->link);
                $client = $service->checkUri($alert->link);
            } catch (Exception $e) {
                continue;
            }
            try {
                $ads = $service->parseResponse($client->request());
            } catch (Zend_Http_Client_Exception $e) {
                continue;
            }
            $date->set($alert->date_updated);
            $maxDate = new Zend_Date($alert->date_updated);
            $newAds = array();
            $cities = array();
            if ($alert->cities) {
                $cities = explode("\n", $alert->cities);
            }
            foreach ($ads AS $ad) { /* @var $ad Model_LeBonCoin_Ad */
                if ($ad->getDateUpdated() > $date) {
                    if ($maxDate < $ad->getDateUpdated()) {
                        $maxDate->set($ad->getDateUpdated());
                    }
                    if (!empty($cities) && !in_array($ad->getCity(), $cities)) {
                        continue;
                    }
                    if ($ad->getPrice() && ($ad->getPrice() < $alert->price_min || $ad->getPrice() > $alert->price_max)) {
                        continue;
                    }
                    $this->view->ad = $ad;
                    $newAds[] = $this->view->render('leboncoin/description.phtml');
                }
            }
            $updateAlert = array();
            $config = Zend_Registry::get("config");
            if (count($newAds)) {
                $query = $client->getUri()->getQueryAsArray();
                $subject = 'Alert LeBonCoin';
                if ($alert->title != $alert->link) {
                    $subject .= ' : '.$alert->title;
                } elseif (!empty($query['q'])) {
                    $subject .= ' - '.$query['q'];
                }
                $this->view->alert = $alert;
                $this->view->newAds = $newAds;
                $this->view->currentDate = new Zend_Date();
                $mailAlert = new Zend_Mail('utf-8');
                $mailAlert->setSubject($subject)
                    ->addTo($alert->email);
                if ($config->email && $config->email->from) {
                    $mailAlert->setFrom($config->email->from);
                }
                $mailAlert->setBodyHtml($this->view->render('mail/alerte.phtml'));
                if ($maxDate > $dateNow) {
                    $maxDate->subYear(1);
                }
                $updateAlert['date_updated'] = $maxDate->get(Zend_Date::ISO_8601);
                $updateAlert['counter_ads'] = new Zend_Db_Expr('counter_ads + '.count($newAds));
                $updateAlert['counter_alerts'] = new Zend_Db_Expr('counter_alerts + 1');
                $alert->date_updated = $maxDate->get(Zend_Date::ISO_8601);
                $alert->counter_ads = new Zend_Db_Expr('counter_ads + '.count($newAds));
                $alert->counter_alerts = new Zend_Db_Expr('counter_alerts + 1');
            }
            $updateAlert['date_updated_check'] = new Zend_Db_Expr('NOW()');
            $alert->date_updated_check = new Zend_Db_Expr('NOW()');
            try {
                $alert->getTable()->update($updateAlert, array('id = ?' => $alert->id));
                if (isset($mailAlert)) {
                    $mailAlert->send();
                }
            } catch (Zend_Db_Table_Row_Exception $e) {
                
            }
            unset($mail, $mailAlert);
        }
    }
}
