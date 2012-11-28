<?php

/**
 * The Talk entity is a kind of Publicity.
 */
class Application_Model_TalkMapper extends Application_Model_PublicityMapper
{

    public function __construct()
    {
        $this->setType('Talk');
    }
    
    public function findByUser($id)
    {
        return parent::findByUser($id);
    }

    // The same as parent, just with small type check
    public function save(Application_Model_Publicity $publication)
    {
        if ($publication->getType() != 'Talk') {
            throw new Exception('Incorrect Talk object, type is ' . $publication->getType());
        }
        return parent::save($publication);
    }

    // The same as parent, just with small type check
    public function findById($id)
    {
        $study = parent::findById($id);
        if ($study) {
            if ($study->getType() != 'Talk') {
                return false;
            }
        }
        return $study;
    }

}
