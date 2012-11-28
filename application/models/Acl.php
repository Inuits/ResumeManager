<?php

class Application_Model_Acl extends Zend_Acl {

    public function __construct() {

        $this->addRole('guest');
        $this->addRole('user', 'guest');
        $this->addRole('admin', 'user');

        // Guest.
        $this->add(new Zend_Acl_Resource('guest_res'));
        $this->add(new Zend_Acl_Resource('admin_res'));
        $this->add(new Zend_Acl_Resource('user_res'));

        $this->add(new Zend_Acl_Resource('register_form'));
        $this->add(new Zend_Acl_Resource('login_form'));
        $this->add(new Zend_Acl_Resource('show_own_cv'));
        $this->add(new Zend_Acl_Resource('show_any_cv'));
        $this->add(new Zend_Acl_Resource('edit_own_cv'));
        $this->add(new Zend_Acl_Resource('edit_any_cv'));
        $this->add(new Zend_Acl_Resource('export_own_cv'));
        $this->add(new Zend_Acl_Resource('export_any_cv'));
        $this->add(new Zend_Acl_Resource('show_customers'));
        $this->add(new Zend_Acl_Resource('edit_customers'));
        $this->add(new Zend_Acl_Resource('administer'));


        $this->deny(null, null, null);

        // Guest.
        $this->allow('guest', 'guest_res');
        $this->allow('guest', 'register_form');
        $this->allow('guest', 'login_form');

        // User.
        $this->allow('user', 'user_res');
        $this->allow('user', 'show_own_cv');
        $this->allow('user', 'edit_own_cv');
        $this->allow('user', 'export_own_cv');
        //        $this->allow('user', 'register_form');
        //        $this->allow('user', 'login_form');
        //        $this->allow('user', 'show_any_cv');
        //        $this->allow('user', 'edit_any_cv');
        //        $this->allow('user', 'export_any_cv');
        //        $this->allow('user', 'show_customers');
        //        $this->allow('user', 'edit_customers');
        //        $this->allow('user', 'administer');

        // Admin.
        $this->allow('admin', 'admin_res');
        $this->allow('admin', 'administer');
    }

    public function can($resource, $uid = false) {
        $storage = new Zend_Auth_Storage_Session();
        $user_data = $storage->read();
        $roles = array('guest', 'user', 'admin');
        $user_role = $roles[0];
        if ($user_data) {
            $user_role = $roles[$user_data->user_level];
        }

        if ($uid && !$user_data) {
            $resource = str_replace('__', '_any_', $resource);
        }
        elseif ($uid && ($user_data->uid != $uid)) {
            $resource = str_replace('__', '_any_', $resource);
        }
        elseif ($uid && ($user_data->uid == $uid)) {
            if ($this->isAllowed($user_role, str_replace('__', '_any_', $resource))) {
                return true;
            }
            $resource = str_replace('__', '_own_', $resource);
        }

        if ($this->isAllowed($user_role, 'administer')) {
            return true;
        }
        return $this->isAllowed($user_role, $resource);
    }


}
