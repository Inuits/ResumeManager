<?php

/**
 * The Education entity is a Study with minor specific: ending date is optional.
 */

class Application_Model_EducationMapper extends Application_Model_StudyMapper
{
    public function __construct()
    {
        $this->setType('Education');
    }
    
    public function findByUser($id) 
    {
        return parent::findByUser($id);
    }
    
    // The same as parent, just with small type check
    public function save(Application_Model_Study $study)
    {
        if ($study->getType() != $this->getType()) {
            throw new Exception('Incorrect ' . $this->getType() . ' object, type is ' . $study->getType());
        }
        return parent::save($study);   
    }
    
    // The same as parent, just with small type check
    public function findById($id)
    {
        $study = parent::findById($id);
        if ($study) {
            if ($study->getType() != $this->getType()) {
                return false;
            }
        }
        return $study;
    }
}
