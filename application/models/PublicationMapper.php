<?php

/**
 * The Publication entity is a kind of Publicity.
 */

// TODO: PublicationMapper, TalkMapper, EducationMapper, TrainingMapper can be refactored in a single addon-class.

class Application_Model_PublicationMapper extends Application_Model_PublicityMapper
{
    
    public function __construct()
    {
        $this->setType('Publication');
    }
    
    public function findByUser($id) 
    {
        return parent::findByUser($id);
    }
    
    // The same as parent, just with small type check
    public function save(Application_Model_Publicity $publication) 
    {
        if ($publication->getType() != 'Publication') {
            throw new Exception('Incorrect Publication object, type is ' . $publication->getType());
        }
        return parent::save($publication);   
    }
    
    // The same as parent, just with small type check
    public function findById($id) 
    {
        $study = parent::findById($id);
        if ($study) {
            if ($study->getType() != 'Publication') {
                return false;
            }
        }
        return $study;
    }
}
