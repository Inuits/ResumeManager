<?php

class Application_Model_UserSkillMapper extends Application_Model_UserRelatedMapper
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
            $this->setDbTable('Application_Model_DbTable_UserSkill');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_UserSkill $skill)
    {
        $data = array(
            'sid' => $skill->getSkillId(),
            'uid' => $skill->getUserId()
        );
        if (null === ($id = $skill->getId())) {
            unset($data['usid']);
            $this->getDbTable()->insert($data);
        } else {
            return $this->getDbTable()->update($data, array('usid = ?' => $id));
        }
    }

    public function findById($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $userskill = new Application_Model_UserSkill;
        $userskill->setId($row->usid);
        $userskill->setSkillId($row->sid);
        $userskill->setUserId($row->uid);
        return $userskill;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $userskills = array();
        foreach ($resultSet as $row) {
            $userskill = new Application_Model_Users();
            $users[] = $user;
        }
        return $users;
    }

    /**
     * Returns skill ID only. See also the findAllByUser function.
     */
    public function findByUser($id)
    {
        $where = "uid = " . $id;
        $resultSet = $this->getDbTable()->fetchAll($where);
        $userSkills = array();
        foreach ($resultSet as $row) {
            $userSkills[] = $row->sid;
        }
        return $userSkills;
    }

    public function findAllByUser($uid)
    {
        $return = $this->getDbTable()->getAdapter()->query("SELECT DISTINCT skill.name AS name, skill_category.name AS cat FROM skill_category INNER JOIN skill ON skill.scid = skill_category.scid INNER JOIN user_skill ON user_skill.sid = skill.sid WHERE user_skill.uid = ? ORDER BY skill_category.name ASC, skill.name ASC", $uid)->fetchAll();
        return $return;
    }

    public function delete($sid, $uid)
    {
        $where = "uid = " . $uid . " AND sid = " . $sid;
        return $this->getDbTable()->delete($where);
    }

}