<?php

class Application_Model_Publicity extends Application_Model_BaseInit
{

    protected $_pid;
    protected $_uid;
    protected $_date;
    protected $_title;
    protected $_link;
    protected $_type;
    protected $_description;
    protected $_language = array();

    public function setPid($number)
    {
        $this->_pid = (int) $number;
        return $this;
    }

    public function getPid()
    {
        return $this->_pid;
    }

    public function setId($number)
    {
        $this->_pid = (int) $number;
        return $this;
    }

    public function getId()
    {
        return $this->_pid;
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

    public function setDate($date)
    {
        $this->_date = $date;
        return $this;
    }

    public function getDate()
    {
        if ($this->_date == '0000-00-00') {
            return '';
        }
        return $this->_date;
    }

    public function setTitle($text)
    {
        $this->_title = (string) $text;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setLink($text)
    {
        $this->_link = (string) $text;
        return $this;
    }

    public function getLink()
    {
        return $this->_link;
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
        return '/publicity/edit/pid/' . $this->_pid;
    }

}