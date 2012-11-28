<?php

class Application_Model_CareerCategoryMapper extends Application_Model_UserRelatedMapper
{

    protected $_dbTable;

    public function setDbTable($dbTable) 
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable() 
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_CareerCategory');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_CareerCategory $category) 
    {
        $data = array(
            'ccid' => $category->getCcid(),
            'uid' => $category->getUid(),
            'name' => $category->getName(),
        );
        if (0 == ($id = $category->getCcid())) {
            unset($data['ccid']);
            $this->getDbTable()->insert($data);
        }
        else {
            return $this->getDbTable()->update($data, array('ccid = ?' => $id));
        }
    }

    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("ccid = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (0 == count($result)) {
            return false;
        }
        $row = $result->getRow(0);
        $category = new Application_Model_CareerCategory();
        $category->setCcid($row->ccid);
        $category->setUid($row->uid);
        $category->setName($row->name);
        return $category;
    }

    public function findByUser($id) 
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->order("name DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $categories = array();
        foreach ($resultSet as $row) {
            $category = new Application_Model_CareerCategory($row->ccid, $row->uid, $row->name);
            $categories[] = $category;
        }
        return $categories;
    }

}

