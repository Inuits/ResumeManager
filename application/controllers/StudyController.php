<?php

class StudyController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function addAction()
    {
        print '{
          success: true,
          title: "Sounds like a Chick Flick",
          msg: "That movie title sounds like a chick flick."
        }';
        exit;
        $this->_helper->viewRenderer->setViewScriptPathSpec('_forms/study.phtml');
        $request = $this->getRequest();
        
        $cid = $this->_request->getParam("cid");
        if ($cid) {
            $studyMapper = new Application_Model_StudyMapper();
            $study = $studyMapper->findById($cid);
            $uid = $study->getUid();
        }
        else {
            $study = new Application_Model_Study();
            $uid = $this->_request->getParam("uid");
        }
        
        $type = $this->_request->getParam("type");
        if ($type) {
            $study->setType($type);
        }
        
        if ($uid) {
            $userMapper = new Application_Model_UserMapper();
            $user = $userMapper->findByUser($uid);
        }
        
        $form = new Application_Form_Study($user, $study);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $getValues = $form->getValues();
                $getValues['Type'] = ucfirst($getValues['Type']);
                $study = new Application_Model_Study($getValues);
                if ($getValues['Type'] == 'Education') {
                    $mapper = new Application_Model_EducationMapper();
                }
                elseif ($getValues['Type'] == 'Training') {
                    $mapper = new Application_Model_TrainingMapper();
                }
                else {
                    return false;
                }
                $mapper->save($study);
                return $this->_helper->_redirector->gotoSimple('view', 'user', null, array('uid' => $study->getUid()));
            }
        }
        $this->view->form = $form;
    }
    
    
    public function editAction()
    {
        $this->addAction();
    }
    
    
    public function ajax_addAction()
    {
        if ($type == 'education') {
            $mapper = new Application_Model_EducationMapper();
            $values['Type'] = 'Education';
        }
        elseif ($type == 'training') {
            $mapper = new Application_Model_TrainingMapper();
            $values['Type'] = 'Training';
        }
        $study = new Application_Model_Study($values);
        $mapper->save($study);
        return $study;
    }
    
    
    
    
}

