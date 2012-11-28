<?php

class Application_Model_CareerCategory
{
    protected $_ccid;
    protected $_uid;
    protected $_name;

    function __construct($careerCategoryId = 0, $userId = 0, $categoryName = '<undefined>') 
    {
        $this->_ccid = $careerCategoryId;
        $this->_uid = $userId;
        $this->_name = $categoryName;
    }
    
    public function setCcid($id) 
    {
        $this->_ccid = (int) $id;
        return $this;
    }

    public function getCcid() 
    {
        return $this->_ccid;
    }

    public function setUid($id) 
    {
        $this->_uid = (int) $id;
        return $this;
    }

    public function getUid() 
    {
        return $this->_uid;
    }

    public function setName($text) 
    {
        $this->_name = (string) $text;
        return $this;
    }

    public function getName() 
    {
        return $this->_name;
    }

}

