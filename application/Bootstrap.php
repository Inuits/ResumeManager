<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Start session
     */
    public function _initCoreSession()
    {
        $this->bootstrap('db');
        $this->bootstrap('session');
        if (!Zend_Session::sessionExists()) {
            Zend_Session::rememberMe(864000);
            Zend_Session::start();
        }
    }

}

