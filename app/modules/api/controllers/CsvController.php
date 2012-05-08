<?php

class Api_CsvController extends Zend_Controller_Action
{
    public function exportAction()
    {
        if ($this->_request->getParam('link')) {
            if ($this->_request->getParam('redirect') != 'ok') {
                $this->view->link = $this->_request->getParam('link');
                return;
            }
            $service = new Service_LeBonCoin();
            try {
                $link = $this->_request->getParam('link');
                if (false === strpos($link, "http://")) {
                    $link = base64_decode($link);
                }
                $client = $service->checkUri($link);
            } catch (Exception $e) {
                if ($e->getMessage() == 'URL invalide') {
                    return;
                }
                throw new Exception($e);
            }
            $this->getResponse()->setHeader('Content-Type', 'application/force-download; name="annonces.csv"', true);
            $this->getResponse()->setHeader('Expires', 'Mon, 01 Jan 2001 01:00:00 GMT', true);
            $this->getResponse()->setHeader('Cache-Control', 'no-cache', true);
            $this->getResponse()->setHeader('Cache-Control', 'post-check=0,pre-check=0', true);
            $this->getResponse()->setHeader('Cache-Control', 'max-age=0', true);
            $this->getResponse()->setHeader('Pragma', 'no-cache', true);
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=annonces.csv', true);
            $this->_helper->layout()->disableLayout(true);
            $this->view->ads = $service->parseResponse($client->request());
        }
    }
}
