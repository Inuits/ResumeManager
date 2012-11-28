<?php 

class Application_Model_SkillMapper
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
            $this->setDbTable('Application_Model_DbTable_Skill');
        }
        return $this->_dbTable;
    }

    
    public function findByUser($uid)
    {
        $uid = (int) $uid;
        $actSkill = array();
        $select = $this->getDbTable()->select()->where('uid = '. $uid );
        $result = $this->getDbTable()->fetchAll($select)->toArray();
        if ($result) {
            foreach ($result as $data) {
                $actSkill[$data['sid']] = $data['sid'];
            }
        }
        
        $skillCategoryMapper = new Application_Model_SkillCategoryMapper();
        return $skillCategoryMapper->getAllSkill($actSkill);
    }

    
    public function findUserBySkill($sid)
    {
        $sid = (int) $sid;
        $actSkill = array();
        $select = $this->getDbTable()->select()->where('sid = '. $sid );
        $result = $this->getDbTable()->fetchAll($select)->toArray();
        if ($result) {
            foreach ($result as $data) {
                $actSkill[$data['uid']] = $data['uid'];
            }
        }
        return $actSkill;
    }

    public function save($uid, $userSkill)
    {
        $this->getDbTable()->delete(array('uid = ?' => $uid));
        if($userSkill) {
            foreach ($userSkill as $sid) {
                $this->getDbTable()->insert(array('uid' => $uid, 'sid' => $sid));
            }
        }
    }

}