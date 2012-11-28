<?php

class Application_Model_User extends Application_Model_BaseInit
{

    protected $_uid = '';
    protected $_userLogin = '';
    protected $_password = '';
    protected $_userLevel = '';
    protected $_firstName = '';
    protected $_lastName = '';
    protected $_location = '';
    protected $_birthDate = '';
    protected $_birthPlace = '';
    protected $_socialSecurity = '';
    protected $_nationality = '';
    protected $_company = '';
    protected $_profile = '';
    protected $_lang = '';
    protected $_language = array();

     
    /*
     *  This function can be used to initialize object from DB result object
     */
    public function init($db_object)
    {
        $db_object = (object) $db_object;
        $this->setUid($db_object->uid);
        $this->setUserLogin($db_object->user_login);
        $this->setPassword($db_object->password);
        $this->setUserLevel($db_object->user_level);
        $this->setFirstName($db_object->first_name);
        $this->setLastName($db_object->last_name);
        $this->setLocation($db_object->location);
        $this->setBirthDate($db_object->birth_date);
        $this->setBirthPlace($db_object->birth_place);
        $this->setSocialSecurity($db_object->social_security);
        $this->setNationality($db_object->nationality);
        $this->setCompany($db_object->company);
        $this->setProfile($db_object->profile);
        $this->setLang($db_object->language);
        $this->setLanguage($db_object->lang_data);
        
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


    public function setId($id)
    {
        $this->_uid = (int) $id;
        return $this;
    }

    public function getId()
    {
        return $this->_uid;
    }

    public function setUserLogin($text)
    {
        $this->_userLogin = (string) $text;
    }

    public function getUserLogin()
    {
        return $this->_userLogin;
    }

    public function setPassword($text)
    {
        $this->_password = (string) $text;
        return $this;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setUserLevel($number)
    {
        $this->_userLevel = (int) $number;
        return $this;
    }

    public function getUserLevel()
    {
        if ($this->_userLevel) {
            return $this->_userLevel;
        }
        else {
            return 1;
        }
    }

    public function setFirstName($text)
    {
        $this->_firstName = (string) $text;
        return $this;
    }

    public function getFirstName()
    {
        return $this->_firstName;
    }

    public function setLastName($text)
    {
        $this->_lastName = (string) $text;
        return $this;
    }

    public function getLastName()
    {
        return $this->_lastName;
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

    public function setBirthDate($date)
    {
        $this->_birthDate = (string) $date;
    }

    public function getBirthDate()
    {
        if ($this->_birthDate == '0000-00-00') {
            return '';
        }
        return $this->_birthDate;
    }

    public function setBirthPlace($text)
    {
        $this->_birthPlace = (string) $text;
        return $this;
    }

    public function getBirthPlace()
    {
        return $this->_birthPlace;
    }

    public function setSocialSecurity($text)
    {
        $this->_socialSecurity = (string) $text;
        return $this;
    }

    public function getSocialSecurity()
    {
        return $this->_socialSecurity;
    }

    public function setLang($text)
    {
        $this->_lang = (string) $text;
        return $this;
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function setNationality($text)
    {
        $this->_nationality = (string) $text;
        return $this;
    }

    public function getNationality()
    {
        return $this->_nationality;
    }

    public function setCompany($text)
    {
        $this->_company = (string) $text;
        return $this;
    }

    public function getCompany()
    {
        return $this->_company;
    }

    public function setProfile($text)
    {
        $this->_profile = (string) $text;
        return $this;
    }

    public function getProfile()
    {
        return $this->_profile;
    }

    /*
     * Returns all user's linked items as an array
     *
     * @datatype can be one of 'education', 'build', 'careerCategory', 'career',
     *              'publication', 'skill', 'talk', 'training'
     */
    public function load($datatype)
    {
        $datatype = ucfirst($datatype);
        $classname = 'Application_Model_' . $datatype . 'Mapper';
        $mapper = new $classname;
        $data = $mapper->findByUser($this->_uid);
        return $data;
    }

}