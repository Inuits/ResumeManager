<?php

class Application_Model_CustomerMapper
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
            $this->setDbTable('Application_Model_DbTable_Customer');
        }
        return $this->_dbTable;
    }


    public function delete(Application_Model_Customer $customer)
    {
        return $this->getDbTable()->delete(array('cid = ?' => $customer->getCid()));
    }

    /*
     * If customer ID is 0 this means we adding a customer.
     */
    public function save(Application_Model_Customer $customer)
    {
        $data = array(
            'cid' => $customer->getCid(),
            'name' => $customer->getName(),
            'description' => $customer->getDescription(),
            'lang_data' => $customer->getLanguage(true),
        );
        if (0 == ($id = $customer->getCid())) {
            unset($data['cid']);
            $this->getDbTable()->insert($data);
            return $this->findByName($customer->getName());
        }
        else {
            return $this->getDbTable()->update($data, array('cid = ?' => $id));
        }
    }

    public function existsCustomer($name)
    {
        $where = $this->getDbTable()->select()->where('name = ?', $name);
        if ($this->getDbTable()->fetchAll($where)->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function findByName($name)
    {
        $where = $this->getDbTable()->select()->where('name = ?', $name);
        $resultSet = $this->getDbTable()->fetchAll($where);
        $customer = false;
        foreach ($resultSet as $row) {
            if ($row) {
                $customer = new Application_Model_Customer();
                $customer->setCid($row->cid);
                $customer->setName($row->name);
                $customer->setDescription($row->description);
                $customer->setLanguage($row->lang_data);
            }
        }
        return $customer;
    }

    public function findNameById($id)
    {
        $where = $this->getDbTable()->select()->where('cid = ?', $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        else {
            $row = $result->getRow(0);
            return $row->name;
        }
    }

    public function findById($id)
    {
        $where = $this->getDbTable()->select()->where('cid = ?', $id);
        $result = $this->getDbTable()->fetchAll($where);
        if (count($result) == 0) {
            return false;
        }
        else {
            $row = $result->getRow(0);
            $customer = new Application_Model_Customer();
            $customer->setCid($row->cid);
            $customer->setName($row->name);
            $customer->setDescription($row->description);
            $customer->setLanguage($row->lang_data);
            return $customer;
        }
    }

    public function fetchAll()
    {
        $order = $this->getDbTable()->select()->order("name ASC");
        $resultSet = $this->getDbTable()->fetchAll($order);
        $customers = array();
        foreach ($resultSet as $row) {
            $customer = new Application_Model_Customer();
            $customer->setCid($row->cid);
            $customer->setName($row->name);
            $customer->setDescription($row->description);
            $customer->setLanguage($row->lang_data);
            $customers[] = $customer;
        }
        return $customers;
    }

}