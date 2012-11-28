<?php

class Application_Model_Language {

    protected $_lid;
    protected $_name;

    public function setLid($id) 
    {
        $this->_lid = $id;
        return $this;
    }

    public function getLid()
    {
        return $this->_lid;
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