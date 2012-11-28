<?php

class Application_Model_Certification extends Application_Model_BaseInit {

    protected $_id;
    protected $_uid;
    protected $_name = '';
    protected $_start_date = '';
    protected $_end_date = '';
    protected $_authority = '';
    protected $_number = '';
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

    public function setName($text) {
        $this->_name = (string) $text;
        return $this;
    }

    public function getName() {
        return $this->_name;
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

    public function setAuthority($text) {
        $this->_authority = (string) $text;
        return $this;
    }

    public function getAuthority() {
        return $this->_authority;
    }

    public function setNumber($text) {
        $this->_number = (string) $text;
        return $this;
    }

    public function getNumber() {
        return $this->_number;
    }

    public function getEditLink() {
        return FALSE;
    }

}
