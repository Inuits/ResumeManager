<?php

class Application_Model_SkillCategoryMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_SkillCategory');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_SkillCategory $skill) {
        $data = array(
            'category' => $skill->getCategory(),
            'name' => $skill->getName()
        );
        if (null === ($id = $skill->getId())) {
            $select = $this->getDbTable()->select()->where('category = ?', $data['category'])->where('name = ?', $data['name']);
            $result = $this->getDbTable()->fetchAll($select)->toArray();
            if (!count($result)) {
                return array('id' => $this->getDbTable()->insert($data), 'status' => 'new');
            } else {
                return array('id' => $result[0]['id'], 'status' => 'old');
            }
        } else {
            return $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id) {
        $select = $this->getDbTable()->select()->where('id = ?', $id);
        return $this->getDbTable()->fetchAll($select)->toArray();
    }

    public function getAllCategories() {
        $select = $this->getDbTable()->select()->from('skill_category', array('category'))->group('category')->order('category');
        return $this->getDbTable()->fetchAll($select)->toArray();
    }

    public function getAllSkill($act = array()) {
        $skillCategories = array();
        $select = $this->getDbTable()->select()->order(array('category', 'name'));
        $result = $this->getDbTable()->fetchAll($select)->toArray();
        if ($result) {
            foreach ($result as $data) {
                $actItem = 0;
                if (!empty($act[$data['id']])) {
                    $actItem = 1;
                }
                $skillCategories[$data['category']][] = array('name' => $data['name'], 'act' => $actItem, 'id' => $data['id']);
            }
        }
        return $skillCategories;
    }

    public function findByUser($uid) {
        $outArr = array();
        $tempArr = array();
        $uid = (int) $uid;
        $select = $this->getDbTable()->select()->where('id in (SELECT sid FROM skill WHERE uid = ' . $uid . ')')->order(array('category', 'name'));
        $result = $this->getDbTable()->fetchAll($select)->toArray();
        if ($result) {
            foreach ($result as $data) {
                $tempArr[$data['category']][] = $data['name'];
            }
            foreach ($tempArr as $key => $var) {
                $skills = implode(', ', $var);
                if ($skills) {
                    $outArr[] = array('type' => $key, 'skill' => $skills);
                }
            }
        }
        return $outArr;
    }

}