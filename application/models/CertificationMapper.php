<?php

class Application_Model_CertificationMapper extends Application_Model_UserRelatedMapper
{

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
        if (!isset($this->_dbTable)) {
            $this->setDbTable('Application_Model_DbTable_Certification');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Certification $certification)
    {
        $data = array(
            'id' => $certification->getId(),
            'uid' => $certification->getUid(),
            'start_date' => $certification->getStartDate(),
            'end_date' => $certification->getEndDate(),
            'name' => $certification->getName(),
            'authority' => $certification->getAuthority(),
            'number' => $certification->getNumber(),
            'lang_data' => $certification->getLanguage(true)
        );
        if (0 == ($id = $certification->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('id = ?' => $id, 'uid = ?' => $data['uid']));
        }
    }

    public function delete(Application_Model_Certification $certification)
    {
        return $this->getDbTable()->delete(array('id = ?' => $certification->getId()));
    }

    public function rowToCertification($row)
    {
        $certification = new Application_Model_Certification();
        $certification->setId($row->id);
        $certification->setUid($row->uid);
        $certification->setStartDate($row->start_date);
        $certification->setEndDate($row->end_date);
        $certification->setAuthority($row->authority);
        $certification->setNumber($row->number);
        $certification->setName($row->name);
        $certification->setLanguage($row->lang_data);
        return $certification;
    }

    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("id = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        $row = $result->getRow(0);
        return $this->rowToCertification($row);
    }

    public function findByUser($id)
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->order("start_date DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $certification = array();
        foreach ($resultSet as $row) {
            $certification[] = $this->rowToCertification($row);
        }
        return $certification;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $certification = array();
        foreach ($resultSet as $row) {
            $certification[] = $this->rowToCertification($row);
        }
        return $certification;
    }
}