<?php

class Application_Form_Study extends Zend_Form
{
    private $user;
    private $study;
    private $lang;
    private $formValue;
    
    
    public function __construct($user, $study, $lang = FALSE) 
    {
    	  $this->user = $user; 
    	  $this->study = $study; 
    	  $this->lang = $lang;
    	  
        parent::__construct();
    }
    
    
    public function study($method)
    {
        if ($this->study && method_exists($this->study, $method)) {
            return $this->study->{$method}();
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
        $this->addElement('text', 'StartDate', array(
            'label'      => 'Start date:',
            'value'      => $this->study('getStartDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
        
        $this->addElement('text', 'EndDate', array(
            'label'      => 'End date:',
            'value'      => $this->study('getEndDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
        
        $this->addElement('text', 'Location', array(
            'label'      => 'Location:',
            'value'      => $this->study('getLocation'),
            'required'   => false,
        ));
        
        $this->addElement('text', 'Achievement', array(
            'label'      => 'Achievement / Certificate or study name:',
            'value'      => $this->study('getAchievement'),
            'required'   => false,
        ));
        
        $this->addElement('textarea', 'Description', array(
            'label'      => 'Description:',
            'value'      => $this->study('getDescription'),
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
        
        $this->addElement('hidden', 'Sid', array(
            'value'      => $this->study('getSid'),
            'required'   => false,
        ));
        
        $this->addElement('hidden', 'Type', array(
            'value'      => $this->study('getType'),
            'required'   => false,
        ));
                     
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }
}

?>