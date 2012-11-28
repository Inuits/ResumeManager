<?php

/*
 * These functions are expected to be implemented in all classes related to User.
 * Any class which can provide records for particular user shall be derived 
 * from this one. We will have formal agreement about functoin names then.
 */

abstract class Application_Model_UserRelatedMapper
{
    /*
     * Getting an information by entity ID. 
     */
    abstract public function findById($id);
    
    /*
     * Getting an information by User ID. 
     */
    abstract public function findByUser($id);
    
    
}
