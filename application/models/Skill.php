<?php
class Application_Model_Skill extends Application_Model_BaseInit
{

    protected $_sid;
    protected $_uid;


    public function setSid($number)
    {
        $this->_sid = (int) $number;
        return $this;
    }

    public function getSid()
    {
        return $this->_sid;
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


}