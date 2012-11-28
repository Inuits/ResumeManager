<?php

class Application_Model_Career extends Application_Model_BaseInit {

    protected $_hid;
    protected $_uid;
    protected $_cid;
    protected $_startDate;
    protected $_endDate = '';
    protected $_function = '';
    protected $_location = '';
    protected $_description = '';
    protected $_language = array();

    public function setHid($id) 
    {
        $this->_hid = (int) $id;
        return $this;
    }

    public function getHid()
    {
        return $this->_hid;
    }

    public function setId($id) 
    {
        $this->_hid = (int) $id;
        return $this;
    }

    public function getId()
    {
        return $this->_hid;
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

    public function setCid($id)
    {
        $this->_cid = (int) $id;
        return $this;
    }

    public function getCid() 
    {
        return $this->_cid;
    }

    public function setCustomerName($name)
    {
        $customerMapper = new Application_Model_CustomerMapper();
        $customer = $customerMapper->findByName($name);
        if (!$customer) {
            $customer = new Application_Model_Customer();
            $customer->setName($name);
            $customer->setDescription('');
            $customer = $customerMapper->save($customer);
        }
        $this->_cid = $customer->getId();
    }

    public function getCustomerName() 
    {
        $customerMapper = new Application_Model_CustomerMapper();
        $customer = $customerMapper->findById($this->_cid);
        $name = '';
        if ($customer) {
            $name = $customer->getName();
        }
        return $name;
    }

    public function setStartDate($date)
    {
        $this->_startDate = $date;
        return $this;
    }

    public function getStartDate()
    {
        return $this->_startDate;
    }

    public function setEndDate($date)
    {
        $this->_endDate = $date;
        return $this;
    }

    public function getEndDate()
    {
        if ($this->_endDate == '0000-00-00') {
            return '';
        }
        return $this->_endDate;
    }

    public function setLocation($text)
    {
        $this->_location = (string) $text;
        return $this;
    }

    public function getLocation()
    {
        return $this->_location;
    }
    
    public function getFunction()
    {
        return $this->_function;
    }

    public function setFunction($text)
    {
        $this->_function = (string) $text;
        return $this;
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

    public function getEditLink()
    {
        return '/career/edit/cid/' . $this->_hid;
    }


    public function getTitle()
    {
        $title = 'Career';
        if ($this->getStartDate()) {
            $title .= ' '. $this->getStartDate();
        }
        if ($this->getEndDate()) {
            $title .= ' - '. $this->getEndDate();
        }
        return $title;
    }

}

