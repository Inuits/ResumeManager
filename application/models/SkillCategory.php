<?php

class Application_Model_SkillCategory
{

    protected $_id;
    protected $_category = '';
    protected $_name = '';


    public function getId()
    {
        return $this->_id;
    }

    public function setId($number)
    {
        $this->_id = (int) $number;
    }

    public function getCategory()
    {
        return $this->_category;
    }

    public function setCategory($name)
    {
        $this->_category = (string) $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = (string) $name;
        return $this;
    }

    public function setFromDB($row)
    {
        $this->setCategoryId($row->id);
        $this->setCategory($row->category);
        $this->setName($row->name);
        return $this;
    }
}