<?php

class FeedController extends Zend_Controller_Action
{
    protected $_service;

    public function init()
    {
        $this->_service = new Service_LeBonCoin();
    }

    public function creerAction()
    {
        $link = $this->_request->getParam('link');
        if ($link) {
            $this->_helper->redirector->gotoUrlAndExit(
                "http://".$_SERVER["HTTP_HOST"]."/rss?link=".$link
            );
        } else {
            $this->_helper->redirector("index", "rss", "default");
        }
    }

    public function refreshAction()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/xml', true)
            ->setHeader('Cache-Control', 'no-cache, must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Expires', 0, true)
            ->setHeader('Progma', 'private', true);
        $this->_helper->layout()->disableLayout(true);

        $id = str_replace(".rss", "", $this->_request->getParam("id"));
        $file = APPLICATION_PATH."/../www/feed/refresh/id/".$id.".rss";
        if (file_exists($file)) {
            $this->view->feed = file_get_contents($file);
            return;
        }
        $tableFeed = new Model_DbTable_Feed();
        $feedRow = $tableFeed->fetchRow(array("link_md5 = ?" => $id));
        if (!$feedRow) {
            $this->notFound();
            return;
        }
        $feedRow->date_updated = new Zend_Db_Expr('NOW()');
        $feedRow->counter = new Zend_Db_Expr('counter + 1');
        $client = $this->_service->checkUri($feedRow->link);

        $feedRow->save();

        $query = $client->getUri()->getQueryAsArray();
        $title = 'LeBonCoin';
        if (!empty($query['q'])) {
            $title .= ' - '.$query['q'];
        }
        $feed = new Zend_Feed_Writer_Feed();
        $feed->setTitle($title);
        $feed->setLink('http://www.leboncoin.fr');
        $feed->setFeedLink($client->getUri()->getUri(), 'rss');
        $feed->setDescription('Flux RSS de la recherche : '.$client->getUri()->getUri());
        $ads = $this->_service->parseResponse($client->request());
        // génération du flux
        foreach ($ads AS $ad) {
            $description = $title = '';
            if ($ad->getUrgent()) {
                $title .= '[URGENT] ';
            }
            $title .= $ad->getTitle();
            if ($ad->getPrice()) {
                $title .= ' ('.number_format($ad->getPrice(), 0, ',', ' ').' €)';
            }
            $this->view->ad = $ad;
            try {
                $entry = $feed->createEntry();
                $entry->setTitle($title);
                $entry->setDateCreated($ad->getDate());
                $entry->setDateModified($ad->getDateUpdated());
                $entry->setLink($ad->getLink());
                $entry->setDescription($this->view->render('leboncoin/description.phtml'));
                $feed->addEntry($entry);
            } catch (Exception $e) {
            }
        }
        $this->view->feed = $feed->export('rss');

        // mise en cache
        if (is_writable(APPLICATION_PATH."/../www/feed/refresh/id")) {
            file_put_contents($file, $this->view->feed);
        }
    }
}
