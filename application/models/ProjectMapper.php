<?php

class Application_Model_ProjectMapper extends Application_Model_UserRelatedMapper
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
            $this->setDbTable('Application_Model_DbTable_Project');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Project $project)
    {
        $data = array(
            'id' => $project->getId(),
            'uid' => $project->getUid(),
            'start_date' => $project->getStartDate(),
            'end_date' => $project->getEndDate(),
            'name' => $project->getName(),
            'occupation' => $project->getOccupation(),
            'url' => $project->getUrl(),
            'description' => $project->getDescription(),
            'lang_data' => $project->getLanguage(true)
        );
        if (0 == ($id = $project->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('id = ?' => $id, 'uid = ?' => $data['uid']));
        }
    }

    public function delete(Application_Model_Project $project)
    {
        return $this->getDbTable()->delete(array('id = ?' => $project->getId()));
    }

    public function rowToProject($row)
    {
        $project = new Application_Model_Project();
        $project->setId($row->id);
        $project->setUid($row->uid);
        $project->setStartDate($row->start_date);
        $project->setEndDate($row->end_date);
        $project->setName($row->name);
        $project->setOccupation($row->occupation);
        $project->setUrl($row->url);
        $project->setDescription($row->description);
        $project->setLanguage($row->lang_data);
        return $project;
    }

    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where("id = ?", $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        $row = $result->getRow(0);
        return $this->rowToProject($row);
    }

    public function findByUser($id)
    {
        $where = $this->getDbTable()->select()->where("uid = ?", $id)->order("start_date DESC");
        $resultSet = $this->getDbTable()->fetchAll($where);
        $project = array();
        foreach ($resultSet as $row) {
            $project[] = $this->rowToProject($row);
        }
        return $project;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $project = array();
        foreach ($resultSet as $row) {
            $project[] = $this->rowToProject($row);
        }
        return $project;
    }
}