<?php

class Application_Model_BaseInit
{
    public function setLanguage($array)
    {
        if ($array && !is_array($array)) {
            $this->_language = @unserialize($array);
        }
        else {
            $this->_language = (array) $array;
        }
        return $this;
    }

    public function initLanguage($lang)
    {
        if ($lang == 'en') {
            return true;
        }
        $language = $this->getLanguage();
        if (empty($language[$lang])) {
            return false;
        }
        else {
            $this->__construct($language[$lang]);
            return true;
        }
    }

    public function getLanguage($serialize = false, $to_form = false)
    {
        if ($to_form) {
            $out = array();
            if($this->_language) {
                foreach ($this->_language as $key => $data) {
                    if ($data) {
                        foreach ($data as $key2 => $data2) {
                            $out[$key .':'. $key2] = $data2;
                        }
                    }
                }
            }
            return $out;
        }
        if ($serialize) {
            return serialize($this->_language);
        }
        else {
            return $this->_language;
        }
    }


    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Customer property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Customer property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    function lcfirst($str)
    {
        return (string)(strtolower(substr($str,0,1)).substr($str,1));
    }

    /**
     * Get result from all get methods.
     */
    public function getArray()
    {
        $metods = get_class_methods($this);
        $out = array();
        foreach ($metods as $metod) {
            if (substr($metod, 0, 3) == 'get' && $metod != 'getArray') {
                $out[$this->lcfirst(substr($metod, 3))] = $this->$metod();
            }
        }
        return $out;
    }
}
