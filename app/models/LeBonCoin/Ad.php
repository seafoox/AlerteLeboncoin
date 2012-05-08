<?php


class Model_LeBonCoin_Ad
{
    protected $_id;
    protected $_link;
    protected $_title;
    protected $_description;
    protected $_price;
    protected $_date;
    protected $_date_updated;
    protected $_category;
    protected $_county;
    protected $_city;
    protected $_professional;
    protected $_thumbnail_link;
    protected $_urgent;
    
    
    /**
    * @param int $id
    * @return Model_LeBonCoin_Ad
    */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
    * @return int
    */
    public function getId()
    {
        return $this->_id;
    }
    
    
    /**
     * @param string $link
     * @return Model_LeBonCoin_Ad
     */
    public function setLink($link)
    {
        $this->_link = $link;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLink()
    {
        return $this->_link;
    }
    
    
    /**
     * @param string $title
     * @return Model_LeBonCoin_Ad
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    
    /**
     * @param string $description
     * @return Model_LeBonCoin_Ad
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    
    /**
     * @param int $price
     * @return Model_LeBonCoin_Ad
     */
    public function setPrice($price)
    {
        $this->_price = (int) preg_replace('/[^0-9]*/', '', $price);
        return $this;
    }
    
    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->_price;
    }
    
    
    /**
     * @param Zend_Date $date
     * @return Model_LeBonCoin_Ad
     */
    public function setDate(Zend_Date $date)
    {
        $this->_date = $date;
        return $this;
    }
    
    /**
     * @return Zend_Date
     */
    public function getDate($param = null)
    {
        if ($param !== null && $this->_date instanceof Zend_Date) {
            return $this->_date->get($param);
        }
        return $this->_date;
    }
    
    
    /**
    * @param Zend_Date $date_updated
    * @return Model_LeBonCoin_Ad
    */
    public function setDateUpdated($date_updated)
    {
        $this->_date_updated = $date_updated;
        return $this;
    }
    
    /**
    * @return Zend_Date
    */
    public function getDateUpdated($param = null)
    {
        if ($param !== null && $this->_date_updated instanceof Zend_Date) {
            return $this->_date_updated->get($param);
        }
        return $this->_date_updated;
    }
    
    
    /**
     * @param string $category
     * @return Model_LeBonCoin_Ad
     */
    public function setCategory($category)
    {
        $this->_category = $category;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->_category;
    }
    
    
    /**
     * @param string $county
     * @return Model_LeBonCoin_Ad
     */
    public function setCounty($county)
    {
        $this->_county = $county;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->_county;
    }
    
    
    /**
     * @param string $city
     * @return Model_LeBonCoin_Ad
     */
    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCity()
    {
        return $this->_city;
    }
    
    
    /**
     * @param bool $professionnal
     * @return Model_LeBonCoin_Ad
     */
    public function setProfessionnal($professionnal)
    {
        $this->_professionnal = $professionnal;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getProfessionnal()
    {
        return $this->_professionnal;
    }
    
    
    /**
     * @param string $thumbail
     * @return Model_LeBonCoin_Ad
     */
    public function setThumbnailLink($thumbail)
    {
        $this->_thumbnail_link = $thumbail;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getThumbnailLink()
    {
        return $this->_thumbnail_link;
    }
    
    
    /**
     * @param bool $urgent
     * @return Model_LeBonCoin_Ad
     */
    public function setUrgent($urgent)
    {
        $this->_urgent = (bool)$urgent;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getUrgent()
    {
        return $this->_urgent;
    }
}