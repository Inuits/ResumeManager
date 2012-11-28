<?php

class CustomerController extends Zend_Controller_Action
{
  
    public function init()
    {
        /* Initialize action controller here */
    }

    
    public function indexAction()
    {
        $this->view->data = null;
    }

    
    public function modifyAction()
    {
        $this->_helper->viewRenderer->setViewScriptPathSpec('_forms/customer.phtml');
        $request = $this->getRequest();
        $customer = '';
        $cid = $this->_request->getParam("cid");
        if ($cid) {
            $customerMapper = new Application_Model_CustomerMapper();
            $customer = $customerMapper->findById($cid);
        }
        
        $form  = new Application_Form_Customer($customer);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {                
                $customer = new Application_Model_Customer($form->getValues());
                $mapper  = new Application_Model_CustomerMapper();
                $mapper->save($customer);
                return $this->_helper->redirector('index');
            }
        }
 
        $this->view->form = $form;    
    }

    
    public function addAction()
    {
        $this->_helper->viewRenderer->setViewScriptPathSpec('_forms/customer.phtml');
        $request = $this->getRequest();
        $customer = new Application_Model_Customer();
        $form  = new Application_Form_Customer($customer);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $customer = new Application_Model_Customer($form->getValues());
                $mapper  = new Application_Model_CustomerMapper();
                $mapper->save($customer);
                return $this->_helper->redirector('index');
           }
        }
 
        $this->view->form = $form;    
    }


}



