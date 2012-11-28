<?php

class Application_Model_UserSkill
{

    protected $_id;
    protected $_sid;
    protected $_uid;

    public function setId($number)
    {
        $this->_id = (int) $number;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setSkillId($number)
    {
        $this->_sid = (int) $number;
        return $this;
    }

    public function getSkillId()
    {
        return $this->_sid;
    }

    public function setUserId($number)
    {
        $this->_uid = (int) $number;
        return $this;
    }

    public function getUserId()
    {
        return $this->_uid;
    }

}