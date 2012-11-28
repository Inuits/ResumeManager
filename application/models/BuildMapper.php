<?php

class Application_Model_BuildMapper extends Application_Model_UserRelatedMapper
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
            $this->setDbTable('Application_Model_DbTable_Build');
        }
        return $this->_dbTable;
    }

    
    
    
    public function save(Application_Model_Build $build) 
    {
        $data = array(
            'id' => $build->getId(),
            'uid' => $build->getUid(),
            'title' => $build->getTitle(),
            'lang' => $build->getLang(),
            'created' => $build->getCreated(),
            'changed' => $build->getChanged(),
            'resume_data' => serialize($build->getData())
        );
        if (0 == ($id = $build->getId())) {
            unset($data['id']);
            return $this->getDbTable()->insert($data);
        }
        else {
            return $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
    

    public function findById($id) 
    {
        $where = $this->getDbTable()->select()->where("id = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        $row = $result->getRow(0);
        $build = new Application_Model_Build();
        $build->init($row);
        return $build;
    }
    

    public function getBuilds($uid) 
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $uid);
        $result = $this->getDbTable()->fetchAll($where)->toArray();
        return $result;
    }
    
    
    public function delete(Application_Model_Build $build)
    {
        return $this->getDbTable()->delete(array('id = ?' => $build->getId()));
    }
    

    public function findByUser($id) 
    {
//        $where = $this->getDbTable()->select()->where("uid = ?", $id)
//                ->order("build_date DESC");
//        $resultSet = $this->getDbTable()->fetchAll($where);
//        $build = array();
//        foreach ($resultSet as $row) {
//            $buildItem = new Application_Model_Build();
//            $build->init($row);
//            $build[] = $buildItem;
//        }
//        return $build;
    }
   
}