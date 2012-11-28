<?php

class Application_Form_Career extends Zend_Form
{
    private $user;
    private $career;
    private $lang;
    private $formValue;
    
    
    public function __construct($user, $career, $lang = FALSE) 
    {
    	  $this->user = $user; 
    	  $this->career = $career; 
    	  $this->lang = $lang;
    	  
        parent::__construct();
    }
    
    
    public function career($method)
    {
        if ($this->career && method_exists($this->career, $method)) {
            return $this->career->{$method}();
        }
        else {
            return '';
        }
        
    }
    
    
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        
        /**
         * Form
         */
        $this->addElement('text', 'Cid', array(
            'label'      => 'Customer:',
            'value'      => $this->career('getCid'),
            'required'   => false,
        ));
        
        $this->addElement('text', 'StartDate', array(
            'label'      => 'Start date:',
            'value'      => $this->career('getStartDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
        
        $this->addElement('text', 'EndDate', array(
            'label'      => 'End date:',
            'value'      => $this->career('getEndDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
        
        $this->addElement('text', 'Location', array(
            'label'      => 'Location:',
            'value'      => $this->career('getLocation'),
            'required'   => false,
        ));
        
        $this->addElement('textarea', 'Description', array(
            'label'      => 'Description:',
            'value'      => $this->career('getDescription'),
            'required'   => false,
        ));
            
        
        /**
         * Buttons
         */
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));
        
        
        /**
         * Hidden value
         */
        $this->addElement('hidden', 'Uid', array(
            'value'      => $this->user->getUid(),
            'required'   => false,
        ));
        
        $this->addElement('hidden', 'Hid', array(
            'value'      => $this->career->getHid(),
            'required'   => false,
        ));
                     
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }
}

?>