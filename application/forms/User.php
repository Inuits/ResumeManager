<?php

class Application_Form_User extends Zend_Form
{
    private $user;
    private $lang;
    private $formValue;
    
    
    public function __construct($user, $lang = FALSE) 
    {
    	  $this->user = $user; 
    	  $this->lang = $lang;
    	  
        parent::__construct();
    }
    
    
    public function user($method)
    {
        if ($this->user && method_exists($this->user, $method)) {
            return $this->user->{$method}();
        }
        else {
            return '';
        }
        
    }
    
    
    public function init()
    {
        
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        
        $this->addElement('text', 'FirstName', array(
            'label'      => 'First name:',
            'value'      => $this->user('getFirstName'),
            'required'   => true,
        ));
        
        $this->addElement('text', 'LastName', array(
            'label'      => 'Last name:',
            'value'      => $this->user('getLastName'),
            'required'   => true,
        ));
 
        $this->addElement('text', 'Location', array(
            'label'      => 'Location:',
            'value'      => $this->user('getLocation'),
            'required'   => false,
        ));
 
        $this->addElement('text', 'BirthDate', array(
            'label'      => 'Birth date:',
            'value'      => $this->user('getBirthDate'),
            'required'   => false,
        		'validators' => array('Date'),
        ));
 
        $this->addElement('text', 'BirthPlace', array(
            'label'      => 'Birth place:',
            'value'      => $this->user('getBirthPlace'),
            'required'   => false,
        ));
 
        $this->addElement('text', 'SocialSecurity', array(
            'label'      => 'Social security:',
            'value'      => $this->user('getSocialSecurity'),
            'required'   => false,
        ));

        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));
        
        
        $this->addElement('hidden', 'uid', array(
            'value'      => $this->user('getUid'),
            'required'   => false,
        ));
        
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }
}

?>