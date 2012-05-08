<?php

class Service_LeBonCoin
{
    /**
     * @param Zend_Uri_Http|string $uri
     * @return Zend_Http_Client
     */
    public function checkUri($uri)
    {
        $aUri = parse_url($uri);
        if (!isset($aUri["scheme"]) || $aUri["scheme"] != "http" || !preg_match("#(mobile|www)\.leboncoin\.fr$#", $aUri["host"])) {
            throw new Exception('URL invalide');
        }
        $uri = Zend_Uri::factory($aUri["scheme"]);
        $uri->setHost($aUri["host"]);
        $uri->setPath($aUri["path"]);
        if (isset($aUri["query"])) {
            $uri->setQuery($aUri["query"]);
        }
        return new Zend_Http_Client($uri);
    }

    public function parseResponse($response)
    {
        if ($response instanceof Zend_Http_Response) {
            $response = $response->getBody();
        }

        $dateToday = new Zend_Date();
        $dateToday->setTime('23:59:59');
        $dateYesterday = new Zend_Date();
        $dateYesterday->sub(1, Zend_Date::DAY);
        $date = new Zend_Date();

        $dom = new Zend_Dom_Query($response);
        $results = $dom->query(".list-ads .ad-lbc");
        if (count($results) == 0) {
            return array();
        }
        $ads = array();
        foreach ($results AS $result) {
            $ad = new Model_LeBonCoin_Ad();
            $ad->setProfessionnal(false)->setUrgent(false);
            preg_match('/([0-9]+)\.htm.*/', $result->parentNode->getAttribute("href"), $m);
            $ad->setLink($result->parentNode->getAttribute("href"))
                ->setId($m[1]);
            foreach ($result->getElementsByTagName("div") AS $node) {
                if ($node->hasAttribute("class")) {
                    $class = $node->getAttribute("class");
                    if ($class == "date") {
                        $dateStr = preg_replace("#\s+#", " ", trim($node->nodeValue));
                        $aDate = explode(' ', $dateStr);
                        if (false !== strpos($dateStr, 'Aujourd')) {
                            $date->setDate($dateToday);
                        } elseif (false !== strpos($dateStr, 'Hier')) {
                            $date->setDate($dateYesterday);
                        } else {
                            switch ($aDate[1]) {
                                case'jan': $aDate[1] = 'janv.'; break;
                                case'fév': $aDate[1] = 'févr.'; break;
                                case'dec': $aDate[1] = 'déc.'; break;
                                case'juillet': $aDate[1] = 'juil.'; break;
                                case'mars':
                                case'mai':
                                case'juin':
                                case'août':
                                    break;
                                default: $aDate[1] .= '.';
                            }
                            try {
                                $date->set($aDate[1], Zend_Date::MONTH_NAME_SHORT);
                            } catch (Zend_Date_Exception $e) {
                                if (Zend_Registry::get('logger')) {
                                    Zend_Registry::get('logger')->err(
                                        'Pas de correspondance de date pour '.
                                        $dateStr.'. Nous sommes le : '.
                                        $dateToday->get(Zend_Date::DATETIME).'. Msg: '.$e->getMessage()
                                    );
                                }
                                break;
                            }
                            $date->set($aDate[0], Zend_Date::DAY);
                        }
                        $date->setTime($aDate[count($aDate) - 1]);
                        if ($dateToday < $date) {
                            $date->subYear(1);
                        }
                        $ad->setDate(clone $date)
                            ->setDateUpdated(clone $date);
                    } elseif ($class == "title") {
                        $ad->setTitle(trim($node->nodeValue));
                    } elseif ($class == "image") {
                        $img = $node->getElementsByTagName("img");
                        if ($img->length > 0) {
                            $img = $img->item(0);
                            $ad->setThumbnailLink($img->getAttribute("src"));
                        }
                    } elseif ($class == "placement") {
                        $placement = $node->nodeValue;
                        if (false !== strpos($placement, "/")) {
                            $placement = explode("/", $placement);
                            $ad->setCounty(trim($placement[1]))
                                ->setCity(trim($placement[0]));
                        } else {
                            $ad->setCounty(trim($placement));
                        }
                    } elseif ($class == "category") {
                        $category = $node->nodeValue;
                        if (false !== strpos($category, "(pro)")) {
                            $ad->setProfessionnal(true);
                        }
                        $ad->setCategory(trim(str_replace("(pro)", "", $category)));
                    } elseif ($class == "price") {
                        if (preg_match("#[0-9 ]+#", $node->nodeValue, $m)) {
                            $ad->setPrice((int)str_replace(" ", "", trim($m[0])));
                        }
                    } elseif ($class == "urgent") {
                        $ad->setUrgent(true);
                    }
                }
            }
            if ($ad->getDate()) {
                $ads[$ad->getId()] = $ad;
            }
        }
        return $ads;
    }

    public function findAlertMailByKey($key)
    {
        $table = new Zend_Db_Table('AlertMail');
        return $table->fetchRow(array('control_key = ?' => $key));
    }

    public function deleteAlertMail($key, Model_User $user = null)
    {
        $where = array();
        if ($user !== null) {
            $where['user_id = ?'] = $user->getId();
        }
        $table = new Zend_Db_Table('AlertMail');
        if (is_array($key)) {
            $datas = array_filter($key, 'is_numeric');
            if ($datas) {
                $where['id IN (?)'] = $datas;
                return $table->delete($where);
            }
            return;
        }
        $where['control_key = ?'] = $key;
        return $table->delete($where);
    }

    public function validateAlertMail($key)
    {
        $table = new Zend_Db_Table('AlertMail');
        $row = $table->fetchRow(array('control_key = ?' => $key));
        if (!$row) {
            return $row;
        }
        $tableUser = new Model_DbTable_User();
        $idUser = $tableUser->fetchRow(
            $tableUser->select()->from($tableUser, array('id'))
                ->where('email = ?', $row->email)
        );
        if ($idUser) {
            $row->user_id = $idUser->id;
        }
        $row->validated = 1;
        $row->save();
        return $row;
    }

    /**
     * Désactive temporairement une alerte
     * @param int $id
     * @param Model_User $user
     * @return Service_LeBonCoin
     */
    public function pauseAlertMail($id, Model_User $user)
    {
        $table = new Zend_Db_Table('AlertMail');
        $alert = $table->fetchRow(array('id = ?' => $id, 'user_id = ?' => $user->getId()));
        if ($alert) {
            $alert->stop = true;
            $alert->save();
        }
        return $this;
    }

    /**
     * Active une alerte
     * @param int $id
     * @param Model_User $user
     * @return Service_LeBonCoin
     */
    public function resumeAlertMail($id, Model_User $user)
    {
        $table = new Zend_Db_Table('AlertMail');
        $alert = $table->fetchRow(array('id = ?' => $id, 'user_id = ?' => $user->getId()));
        if ($alert) {
            $alert->date_updated = new Zend_Db_Expr('NOW()');
            $alert->stop = false;
            $alert->save();
        }
        return $this;
    }

    /**
     * Change l'intervalle de vérification d'une alerte
     * @param Model_User $user
     * @param int $id
     * @param int $interval
     * @return Service_LeBonCoin
     */
    public function changeIntervalAlertMail(Model_User $user, $id, $interval)
    {
        $interval = (int)$interval;
        if (!in_array($interval, array(15, 30, 60, 120, 720, 1440))) {
            return $this;
        }

        $table = new Zend_Db_Table('AlertMail');
        $alert = $table->fetchRow(array('id = ?' => $id, 'user_id = ?' => $user->getId()));

        if ($alert) {
            $alert->check_interval = $interval;
            $alert->save();
        }
        return $this;
    }

    /**
     * Ajoute une alerte mail
     * @param string $email
     * @param string $link
     * @param Model_User $user
     * @return boolean
     */
    public function addAlertMail($email, $link, Model_User $user = null)
    {
        if (is_array($email)) {
            extract($email);
        }
        $table = new Zend_Db_Table('AlertMail');
        $row = $table->createRow();
        $row->email = $email;
        $row->link = $link;
        $row->title = isset($title)?$title:$link;
        $row->date_created = new Zend_Db_Expr('NOW()');
        $row->date_updated = new Zend_Db_Expr('NOW()');
        $row->date_updated_check = new Zend_Db_Expr('NOW()');
        $row->date_revalidated = new Zend_Db_Expr('NOW()');
        $row->control_key = sha1(substr(md5(rand().rand()), 0, 12));

        if ($user !== null) {
            $row->user_id = $user->getId();
            $row->validated = 1;
            $row->email = $email = $user->getEmail();
            $row->check_interval = $user->getDefaultCheckInterval();
        }
        $row->save();

        if ($user === null) {
            $mail = new Zend_Mail('utf-8');
            $config = Zend_Registry::get("config");
            if ($config->email && $config->email->from) {
                $mail->setFrom($config->email->from);
            }
            $mail->addTo($email);
            $mail->setSubject('Validation de votre alerte');
            $mail->setBodyText(
                'Validez l\'alerte à l\'aide du lien suivant :'."\n".
                'http://'.$_SERVER["HTTP_HOST"].'/alerte-mail/valider/key/'.$row->control_key."\n\n".
                'Vous pouvez supprimer l\'alerte à tout moment en cliquant sur ce lien :'."\n".
                'http://'.$_SERVER["HTTP_HOST"].'/alerte-mail/supprimer/key/'.$row->control_key."\n\n".
                'Rappel de votre recherche leboncoin :'."\n".
                $link."\n\n"
            );
            $mail->send();
        }
        return $row;
    }
}
