<?php

class Application_Model_CareerMapper extends Application_Model_UserRelatedMapper
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
            $this->setDbTable('Application_Model_DbTable_Career');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Career $career)
    {
        $data = array(
            'hid' => $career->getHid(),
            'uid' => $career->getUid(),
            'cid' => $career->getCid(),
            'start_date' => $career->getStartDate(),
            'end_date' => $career->getEndDate(),
            'location' => $career->getLocation(),
            'function' => $career->getFunction(),
            'description' => $career->getDescription(),
            'lang_data' => $career->getLanguage(true),
        );
        if (0 == ($id = $career->getHid())) {
            unset($data['hid']);
            $this->getDbTable()->insert($data);
        }
        else {
            return $this->getDbTable()->update($data, array('hid = ?' => $id, 'uid = ?' => $data['uid']));
        }
    }

    public function delete(Application_Model_Career $study)
    {
        return $this->getDbTable()->delete(array('hid = ?' => $study->getHid()));
    }

    // TODO: implement init()
    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("hid = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (0 == count($result)) {
            return false;
        }
        $row = $result->getRow(0);
        $career = new Application_Model_Career();
        $career->setHid($row->hid);
        $career->setUid($row->uid);
        $career->setCid($row->cid);
        $career->setStartDate($row->start_date);
        $career->setEndDate($row->end_date);
        $career->setLocation($row->location);
        $career->setFunction($row->function);
        $career->setDescription($row->description);
        $career->setLanguage($row->lang_data);
        return $career;
    }

    public function findByUser($id)
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->order("start_date DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $careers = array();
        foreach ($resultSet as $row) {
            $career = new Application_Model_Career();
            $career->setHid($row->hid);
            $career->setUid($row->uid);
            $career->setCid($row->cid);
            $career->setStartDate($row->start_date);
            $career->setEndDate($row->end_date);
            $career->setLocation($row->location);
            $career->setFunction($row->function);
            $career->setDescription($row->description);
            $career->setLanguage($row->lang_data);
            $careers[] = $career;
        }
        return $careers;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $careers = array();
        foreach ($resultSet as $row) {
            $career = new Application_Model_Career();
            $career->setHid($row->id);
            $career->setUid($row->uid);
            $career->setCid($row->cid);
            $career->setStartDate($row->start_date);
            $career->setEndDate($row->end_date);
            $career->setLocation($row->location);
            $career->setFunction($row->function);
            $career->setDescription($row->description);
            $career->setLanguage($row->lang_data);
            $careers[] = $career;
        }
        return $careers;
    }

}