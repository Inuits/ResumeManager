<?php

class Application_Model_Project extends Application_Model_BaseInit {

    protected $_id;
    protected $_uid;
    protected $_start_date = '';
    protected $_end_date = '';
    protected $_name = '';
    protected $_occupation = '';
    protected $_url = '';
    protected $_description = '';
    protected $_language = array();

    public function getId() {
        return $this->_id;
    }

    public function setId($number) {
        $this->_id = (int) $number;
        return $this;
    }

    public function setUid($number) {
        $this->_uid = (int) $number;
        return $this;
    }

    public function getUid() {
        return $this->_uid;
    }

    public function setStartDate($date) {
        $this->_start_date = $date;
        return $this;
    }

    public function getStartDate() {
        if ($this->_start_date == '0000-00-00') {
            return '';
        } else {
            return $this->_start_date;
        }
    }

    public function setEndDate($date) {
        $this->_end_date = $date;
        return $this;
    }

    public function getEndDate() {
        if ($this->_end_date == '0000-00-00') {
            return '';
        } else {
            return $this->_end_date;
        }
    }

    public function setDescription($text) {
        $this->_description = (string) $text;
        return $this;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function setUrl($text) {
        $this->_url = (string) $text;
        return $this;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setOccupation($text) {
        $this->_occupation = (string) $text;
        return $this;
    }

    public function getOccupation() {
        return $this->_occupation;
    }

    public function setName($text) {
        $this->_name = (string) $text;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function getEditLink() {
        return FALSE;
    }

}
