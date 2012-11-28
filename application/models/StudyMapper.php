<?php

class Application_Model_StudyMapper extends Application_Model_UserRelatedMapper
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
            $this->setDbTable('Application_Model_DbTable_Study');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Study $study)
    {
        $data = array(
            'sid' => $study->getSid(),
            'uid' => $study->getUid(),
            'achievement' => $study->getAchievement(),
            'type' => $study->getType(),
            'start_date' => $study->getStartDate(),
            'end_date' => $study->getEndDate(),
            'location' => $study->getLocation(),
            'description' => $study->getDescription(),
            'lang_data' => $study->getLanguage(true)
        );
        if (0 == ($id = $study->getSid())) {
            unset($data['sid']);
            $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('sid = ?' => $id, 'uid = ?' => $data['uid']));
        }
    }

    public function delete(Application_Model_Study $study)
    {
        return $this->getDbTable()->delete(array('sid = ?' => $study->getSid()));
    }

    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("sid = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        $row = $result->getRow(0);
        $study = new Application_Model_Study();
        $study->setSid($row->sid);
        $study->setUid($row->uid);
        $study->setAchievement($row->achievement);
        $study->setType($row->type);
        $study->setStartDate($row->start_date);
        $study->setEndDate($row->end_date);
        $study->setLocation($row->location);
        $study->setDescription($row->description);
        $study->setLanguage($row->lang_data);
        return $study;
    }

    public function findByUser($id)
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->where("type = ?", $this->_type)->order("start_date DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $study = array();
        foreach ($resultSet as $row) {
            $studyItem = new Application_Model_Study();
            $studyItem->setSid($row->sid);
            $studyItem->setUid($row->uid);
            $studyItem->setAchievement($row->achievement);
            $studyItem->setType($row->type);
            $studyItem->setStartDate($row->start_date);
            $studyItem->setEndDate($row->end_date);
            $studyItem->setLocation($row->location);
            $studyItem->setDescription($row->description);
            $studyItem->setLanguage($row->lang_data);
            $study[] = $studyItem;
        }
        return $study;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $study = array();
        foreach ($resultSet as $row) {
            $studyItem = new Application_Model_Study();
            $studyItem->setSid($row->sid);
            $studyItem->setUid($row->uid);
            $studyItem->setAchievement($row->achievement);
            $studyItem->setType($row->type);
            $studyItem->setStartDate($row->start_date);
            $studyItem->setEndDate($row->end_date);
            $studyItem->setLocation($row->location);
            $studyItem->setDescription($row->description);
            $studyItem->setLanguage($row->lang_data);
            $study[] = $studyItem;
        }
        return $study;
    }

}