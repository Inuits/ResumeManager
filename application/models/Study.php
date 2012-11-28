<?php

class Application_Model_Study extends Application_Model_BaseInit
{

    protected $_sid;
    protected $_id;
    protected $_uid;
    protected $_achievement = '';
    protected $_start_date = '';
    protected $_end_date = '';
    protected $_location = '';
    protected $_description = '';
    protected $_language = array();
    // This might to be redefined in a subclass, 'Training', 'Education'
    protected $_type;

    public function setSid($number)
    {
        $this->_id = (int) $number;
        return $this;
    }

    public function getSid()
    {
        return $this->_id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($number)
    {
        $this->_id = (int) $number;
        return $this;
    }

    public function setUid($number)
    {
        $this->_uid = (int) $number;
        return $this;
    }

    public function getUid()
    {
        return $this->_uid;
    }

    public function setAchievement($text)
    {
        $this->_achievement = (string) $text;
        return $this;
    }

    public function getAchievement()
    {
        return $this->_achievement;
    }

    public function setTitle($text)
    {
        $this->_achievement = (string) $text;
        return $this;
    }

    public function getTitle()
    {
        return $this->_achievement;
    }

    public function setType($text)
    {
        $this->_type = (string) $text;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setStartDate($date)
    {
        $this->_start_date = $date;
        return $this;
    }

    public function getStartDate()
    {
        if ($this->_start_date == '0000-00-00') {
            return '';
        }
        else {
            return $this->_start_date;
        }
    }

    public function setEndDate($date)
    {
        $this->_end_date = $date;
        return $this;
    }

    public function getEndDate()
    {
        if ($this->_end_date == '0000-00-00') {
            return '';
        }
        else {
            return $this->_end_date;
        }
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
        return '/study/edit/cid/' . $this->_id;
    }

}
