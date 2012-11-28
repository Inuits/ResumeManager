<?php
class Application_Model_Customer extends Application_Model_BaseInit
{
    protected $_cid;
    protected $_name;
    protected $_description;
    protected $_language = array();
    

    public function setCid($number)
    {
    	 $this->_cid = (int) $number;
    	 return $this;
    }
    
    public function getCid()
    {
    	 return $this->_cid;
    }
    

    public function setId($number)
    {
    	 $this->_cid = (int) $number;
    	 return $this;
    }
    
    public function getId()
    {
    	 return $this->_cid;
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
    
    public function getEditLink()
    {
    	 return '/customer/modify/cid/' . $this->_cid;
    }
    
    public function setDescription($text)
    {
    	 $this->_description = (string) $text;
    	 return $this;
    }
    
    public function getDescription()
    {
    	 return $this->_description;
    }
}