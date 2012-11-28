<?php

class Application_Form_Customer extends Zend_Form
{
    private $customer;
    private $lang;
    private $formValue;
    
    
    public function __construct($customer, $lang = FALSE) 
    {
    	  $this->customer = $customer; 
    	  $this->lang = $lang;
    	  
        parent::__construct();
    }
    
    
    public function customer($method)
    {
        if ($this->customer && method_exists($this->customer, $method)) {
            return $this->customer->{$method}();
        }
        else {
            return '';
        }
        
    }
    
    
    public function init()
    {
        
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        // Add the description element
        $this->addElement('text', 'name', array(
            'label'      => 'Name:',
            'value'      => $this->customer('getName'),
            // TODO: add some hints like 'Enter phone and contact name'
            'required'   => true,
        ));
 
        // Add the description element
        $this->addElement('textarea', 'description', array(
            'label'      => 'Description:',
            'value'      => $this->customer('getDescription'),
            // TODO: add some hints like 'Enter phone and contact name'
            'required'   => false,
        ));
 
        // Add the customer id
        $this->addElement('hidden', 'cid', array(
            'value'      => $this->customer('getCid'),
            'required'   => false,
        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));
        
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }
}

?>