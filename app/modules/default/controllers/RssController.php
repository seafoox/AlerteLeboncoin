<?php

class RssController extends Zend_Controller_Action
{
    protected $_service;

    public function init()
    {
        $this->_service = new Service_LeBonCoin();
    }

    public function indexAction() {
        $link = $this->_request->getParam('link');
        if (false === strpos($link, "http://")) {
            $link = base64_decode($link);
        }
        $this->view->link = $link;
        if ($this->_request->isPost()) {
            $link = $this->_request->getPost("link");
            $this->view->link = $link;
            try {
                $client = $this->_service->checkUri($link);
            } catch (Exception $e) {
                if ($e->getMessage() == 'URL invalide') {
                    $this->view->errorUrl = "Cette adresse ne semble pas valide.";
                    return;
                }
            }
            $query = $client->getUri()->getQueryAsArray();
            if (!empty($query['o']) && $query['o'] != 1) {
                unset($query['o']);
                $client->setParameterGet($query);
            }

            // création d'un identifiant basé sur l'adresse de recherche
            $tableFeed = new Model_DbTable_Feed();
            $hash = sha1(uniqid('fireohev6RFV8sdu_ze', true));
            $feedRow = $tableFeed->createRow(array(
                'link_md5' => $hash,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'date_updated' => new Zend_Db_Expr('NOW()'),
                'link' => $client->getUri(true),
                'counter' => 1
            ));
            $feedRow->save();
            $this->_helper->redirector("refresh", "feed", "default", array(
                "id" => $hash.".rss"
            ));
        }
        if ($link) {
            $this->view->link = $link;
        }
    }
}
