<?php

class PublicityController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function addAction()
    {
        $this->_helper->viewRenderer->setViewScriptPathSpec('_forms/publicity.phtml');
        $request = $this->getRequest();
        
        $pid = $this->_request->getParam("pid");
        if ($pid) {
            $publicityMapper = new Application_Model_PublicityMapper();
            $publicity = $publicityMapper->findById($pid);
            $uid = $publicity->getUid();
        }
        else {
            $publicity = new Application_Model_Publicity();
            $uid = $this->_request->getParam("uid");
        }
        
        $type = $this->_request->getParam("type");
        if ($type) {
            $publicity->setType($type);
        }
        
        if ($uid) {
            $userMapper = new Application_Model_UserMapper();
            $user = $userMapper->findByUser($uid);
        }
        
        $form = new Application_Form_Publicity($user, $publicity);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $getValues = $form->getValues();
                $getValues['Type'] = ucfirst($getValues['Type']);
                $publicity = new Application_Model_Publicity($getValues);
                if ($getValues['Type'] == 'Publication') {
                    $mapper = new Application_Model_PublicationMapper();
                }
                elseif ($getValues['Type'] == 'Talk') {
                    $mapper = new Application_Model_TalkMapper();
                }
                else {
                    return false;
                }
                $mapper->save($publicity);
                return $this->_helper->_redirector->gotoSimple('view', 'user', null, array('uid' => $publicity->getUid()));
            }
        }
        $this->view->form = $form;
    }

    public function editAction()
    {
        $this->addAction();
    }


}

