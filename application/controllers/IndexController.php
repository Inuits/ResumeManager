<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $storage = new Zend_Auth_Storage_Session();
        $user_data = $storage->read();
        if ($user_data && $user_data->uid) {
            return $this->_helper->_redirector->gotoSimple('view', 'user', null, array('uid' => $user_data->uid));
        }
    }


}

