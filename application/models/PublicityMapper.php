<?php

class Application_Model_PublicityMapper extends Application_Model_UserRelatedMapper
{

    protected $_dbTable;
    protected $_type;

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }
     

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
            $this->setDbTable('Application_Model_DbTable_Publicity');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Publicity $publicity)
    {
        $data = array(
            'pid' => $publicity->getPid(),
            'uid' => $publicity->getUid(),
            'date' => $publicity->getDate(),
            'title' => $publicity->getTitle(),
            'link' => $publicity->getLink(),
            'type' => $publicity->getType(),
            'description' => $publicity->getDescription(),
            'lang_data' => $publicity->getLanguage(true),
        );
        if (0 == ($id = $publicity->getPid())) {
            unset($data['pid']);
            $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('pid = ?' => $id, 'uid = ?' => $data['uid']));
        }
    }


    public function delete(Application_Model_Publicity $publicity)
    {
        return $this->getDbTable()->delete(array('pid = ?' => $publicity->getPid()));
    }


    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("pid = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (0 == count($result)) {
            return false;
        }
        $row = $result->getRow(0);
        $publication = new Application_Model_Publicity();
        $publication->setPid($row->pid);
        $publication->setUid($row->uid);
        $publication->setDate($row->date);
        $publication->setTitle($row->title);
        $publication->setLink($row->link);
        $publication->setType($row->type);
        $publication->setDescription($row->description);
        $publication->setLanguage($row->lang_data);
        return $publication;
    }

    public function findByUser($id)
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->where("type = ?", $this->_type)->order("date DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $publications = array();
        foreach ($resultSet as $row) {
            $publication = new Application_Model_Publicity();
            $publication->setPid($row->pid);
            $publication->setUid($row->uid);
            $publication->setDate($row->date);
            $publication->setTitle($row->title);
            $publication->setLink($row->link);
            $publication->setType($row->type);
            $publication->setDescription($row->description);
            $publication->setLanguage($row->lang_data);
            $publications[] = $publication;
        }
        return $publications;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $publications = array();
        foreach ($resultSet as $row) {
            $publication = new Application_Model_Publicity();
            $publication->setPid($row->pid);
            $publication->setUid($row->uid);
            $publication->setDate($row->date);
            $publication->setTitle($row->title);
            $publication->setLink($row->link);
            $publication->setType($row->type);
            $publication->setDescription($row->description);
            $publication->setLanguage($row->lang_data);
            $publications[] = $publication;
        }
        return $publications;
    }

}