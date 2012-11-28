<?php

class Application_Form_Publicity extends Zend_Form
{
    private $user;
    private $publicity;
    private $lang;
    private $formValue;
    
    
    public function __construct($user, $publicity, $lang = FALSE) 
    {
    	  $this->user = $user; 
    	  $this->publicity = $publicity; 
    	  $this->lang = $lang;
    	  
        parent::__construct();
    }
    
    
    public function publicity($method)
    {
        if ($this->publicity && method_exists($this->publicity, $method)) {
            return $this->publicity->{$method}();
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
        
        $this->addElement('text', 'Title', array(
            'label'      => 'Title:',
            'value'      => $this->publicity('getTitle'),
            'required'   => false,
        ));
        
        $this->addElement('text', 'Date', array(
            'label'      => 'Date:',
            'value'      => $this->publicity('getDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
        
        $this->addElement('text', 'Link', array(
            'label'      => 'Link:',
            'value'      => $this->publicity('getLink'),
            'required'   => false,
        		'validators' => array('Hostname'),
        ));
        
        $this->addElement('textarea', 'Description', array(
            'label'      => 'Description:',
            'value'      => $this->publicity('getDescription'),
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
        
        $this->addElement('hidden', 'Pid', array(
            'value'      => $this->publicity('getPid'),
            'required'   => false,
        ));
        
        $this->addElement('hidden', 'Type', array(
            'value'      => $this->publicity('getType'),
            'required'   => false,
        ));
        
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }
}

?>