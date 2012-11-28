<?php

class Application_Model_UserMapper extends Application_Model_UserRelatedMapper
{

    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)){
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract){
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_User');
        }
        return $this->_dbTable;
    }

    public function register(Application_Model_User $user)
    {
        $data = array(
            'user_login' => $user->getUserLogin(),
            'password' => $user->getPassword(),
        );
        return $this->getDbTable()->insert($data);
    }



    public function addLdapUser($ldapUserData, $pass) {
        if($this->fetchByLogin($ldapUserData->uid)) {
            return true;
        }
        else {
            $user = new Application_Model_User();
            $user->setUserLogin($ldapUserData->uid);
            $user->setLastName($ldapUserData->sn);
            $user->setFirstName($ldapUserData->givenname);
            $user->setPassword($pass);
            $this->save($user);
        }
    }


    public function save(Application_Model_User $user)
    {
        $data = array(
            'uid' => $user->getUid(),
            'user_login' => $user->getUserLogin(),
            'password' => $user->getPassword(),
            'user_level' => $user->getUserLevel(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'location' => $user->getLocation(),
            'birth_date' => $user->getBirthDate(),
            'birth_place' => $user->getBirthPlace(),
            'social_security' => $user->getSocialSecurity(),
            'language' => $user->getLang(),
            'nationality' => $user->getNationality(),
            'company' => $user->getCompany(),
            'profile' => $user->getProfile(),
            'lang_data' => $user->getLanguage(true),
        );
        if (!$user->getUid()){
            unset($data['uid']);
            return $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('uid = ?' => $user->getUid()));
        }
    }

    public function saveProfile(Application_Model_User $user)
    {
        $data = array(
            'profile' => $user->getProfile()
        );
        if ($user->getUid()) {
            return $this->getDbTable()->update($data, array('uid = ?' => $user->getUid()));
        }
        return false;
    }

    public function findByUser($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)){
            return;
        }
        $row = $result->current();
        $user = new Application_Model_User();
        $user->init($row);
        return $user;
    }

    public function loadDataVariants($id)
    {
        $outArr = array();
        $select = $this->getDbTable()->select()->from('user', array($id))->group($id)->order($id . ' ASC');
        $resultArr = $this->getDbTable()->fetchAll($select)->toArray();
        foreach ($resultArr as $data) {
            $val = array_pop($data);
            if ($val !== '') {
                $outArr[] = array('key' => $val);
            }
        }
        return $outArr;
    }

    public function findById($id)
    {
        return $this->findByUser($id);
    }

    public function fetchByLogin($email)
    {
        $where = $this->getDbTable()->select()->where("user_login = ?", $email);
        $result = $this->getDbTable()->fetchAll($where);
        if ($result->count() == 0) {
            return false;
        }
        $row = $result->getRow(0);
        $user = new Application_Model_User();
        $user->init($row);
        return $user;
    }

    public function fetchAll()
    {
        $order = $this->getDbTable()->select()->order("last_name ASC");
        $resultSet = $this->getDbTable()->fetchAll($order);
        $users = array();
        foreach ($resultSet as $row) {
            $user = new Application_Model_User();
            $user->init($row);
            $users[] = $user;
        }
        return $users;
    }
}