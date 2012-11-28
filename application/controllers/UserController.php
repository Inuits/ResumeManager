<?php

class UserController extends Zend_Controller_Action
{
    public function init()
    {
        $this->acl = new Application_Model_Acl();
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->info = '';
    }

    public function viewAction()
    {
        $storage = new Zend_Auth_Storage_Session();
        $user_data = $storage->read();
        $uid = $this->_request->getParam("uid");
        if ($uid && $this->acl->can('show__cv', $uid)) {
            $customerMapper = new Application_Model_UserMapper();
            $user = $customerMapper->findById($uid);
            $this->view->uid = $uid;
            $this->view->user = $user->getArray();
        }
        elseif ($user_data && $user_data->uid) {
            return $this->_helper->_redirector->gotoSimple('view', 'user', null, array('uid' => $user_data->uid));
        }
    }


    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }


}
