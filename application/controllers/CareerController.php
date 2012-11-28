<?php

class CareerController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function addAction()
    {
        $this->_helper->viewRenderer->setViewScriptPathSpec('_forms/career.phtml');
        $request = $this->getRequest();
        
        $cid = $this->_request->getParam("cid");
        if ($cid) {
            $careerMapper = new Application_Model_CareerMapper();
            $career = $careerMapper->findById($cid);
            $uid = $career->getUid();
        }
        else {
            $career = new Application_Model_Career();
            $uid = $this->_request->getParam("uid");
        }
        
        if ($uid) {
            $userMapper = new Application_Model_UserMapper();
            $user = $userMapper->findByUser($uid);
        }
        
        $form = new Application_Form_Career($user, $career);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $career = new Application_Model_Career($form->getValues());
                $mapper = new Application_Model_CareerMapper();
                $mapper->save($career);
                return $this->_helper->_redirector->gotoSimple('view', 'user', null, array('uid' => $career->getUid()));
            }
        }
 
        $this->view->form = $form;
    }

    public function editAction()
    {
        $this->addAction();
    }


}

