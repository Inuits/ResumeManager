<?php

class AjaxController extends Zend_Controller_Action
{
    protected $_error = array();
    protected $_messages = array();
    protected $authAdapter;


    public function init()
    {
        $storage = new Zend_Auth_Storage_Session();
        $this->user = $storage->read();
        $this->acl = new Application_Model_Acl();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $this->authAdapter->setTableName('user')->setIdentityColumn('user_login')->setCredentialColumn('password');
    }


    /********************************************************************
     * Actions
     ********************************************************************/


    /**
     * Skill tree load.
     */
    public function userskilltreeAction()
    {
        $rootId = 10000;
        $skills = array();
        $skillCategoryMapper = new Application_Model_SkillCategoryMapper();
        $skillsRaw = $skillCategoryMapper->getAllSkill();
        foreach ($skillsRaw as $key => $data) {
            $children = array();
            if (!empty($data)) {
                foreach ($data as $skillInfo) {
                    if ($skillInfo['name']) {
                        $children[] = array(
                            'text' => $skillInfo['name'],
                            'id' => $skillInfo['id'],
                            'leaf' => true,
                        );
                    }
                }
            }
            if ($children) {
                $skills[] = array(
                    'text' => $key,
                    'id' => $rootId++,
                    'cls' => "folder",
                    'children' => $children,
                );
            }
        }
        print json_encode($skills);
    }

    /**
     * Skill load.
     */
    public function userskillAction()
    {
        $uid = $this->_request->getParam("uid");
        if (!$this->acl->can('show__cv', $uid)) {
            return '';
        }
        $mapper = new Application_Model_SkillMapper();
        $userskill = $mapper->findByUser($uid);
        print json_encode($userskill);
    }
    
    /**
     * Skill update.
     */
    public function skillmapperAction()
    {
        $values = $this->removeTags($_POST);
        $uid = $this->_request->getParam("uid");
        if (!$this->acl->can('edit__cv', $uid)) {
            return '';
        }
        $userSkill = array();
        $newCategorySkill = new Application_Model_SkillCategoryMapper();
        $cat_list = array();
        foreach ($newCategorySkill->getAllCategories() as $data) {
            $cat_list[$data['category']] = $data['category'];
        }

        foreach ($values as $key => $val) {
            if ($val != '- skip -' && $cat_list[$val]) {
                $key = str_replace('_', ' ', $key);
                $newSkill = new Application_Model_SkillCategory();
                $newSkill->setCategory($val);
                $newSkill->setName(trim($key));
                $newSkillData = $newCategorySkill->save($newSkill);
                $userSkill[$newSkillData['id']] = $newSkillData['id'];
            }
        }
        $skillMapper = new Application_Model_SkillMapper();
        $skillMapper->save($uid, $userSkill);

        $this->addMessage("Profile has been updated.");
        $this->printMessages();
    }


    /**
     * Skill update.
     */
    public function updateskillAction()
    {
        $values = $this->removeTags($_POST);
        $uid = $this->_request->getParam("uid");
        if (!$this->acl->can('edit__cv', $uid)) {
            return '';
        }
        $userSkill = array();
        $newCategorySkill = new Application_Model_SkillCategoryMapper();
        foreach ($values as $key => $val) {
            if (is_int($key) && $val == 'on') {
                $userSkill[$key] = $key;
            }
            else {
                $arr = explode('_', $key);
                if ($arr[0] == 'new' && empty($arr[2]) && $val == 'on' && $values['new_val_'. $arr[1]]) {
                    $newItemArr = explode(',', $values['new_val_'. $arr[1]]);
                    $skill = $newCategorySkill->find($arr[1]);
                    $skillCat = $skill[0]['category'];
                    if ($skillCat) {
                        foreach ($newItemArr as $skillName) {
                            $newSkill = new Application_Model_SkillCategory();
                            $newSkill->setCategory($skillCat);
                            $newSkill->setName(trim($skillName));
                            $newSkillData = $newCategorySkill->save($newSkill);
                            $userSkill[$newSkillData['id']] = $newSkillData['id'];
                            if ($newSkillData['status'] == 'new') {
                                $this->addMessage("New skill <b>{$skillName}</b>  has been created.");
                            }
                        }
                    }
                }
            }
        }
        if (!empty($values['add_category'])) {
            $category = new Application_Model_SkillCategory();
            $category->setCategory($values['add_category']);
            $newCategorySkill->save($category);
        }
        

        $skillMapper = new Application_Model_SkillMapper();
        $skillMapper->save($uid, $userSkill);

        $this->addMessage("Skill has been updated.");
        print $this->printMessages();
    }

    /**
     * Get store.
     */
    public function userstoreAction()
    {
        $type = $this->_request->getParam("type");
        $mapper = new Application_Model_UserMapper();
        print json_encode($mapper->loadDataVariants($type));
    }


    /**
     * Save action.
     */
    public function saveAction()
    {
        $jsonData = array();
        if ($this->getRequest()->isPost()) {
            $type = $this->_request->getParam("type");
            $values = $this->removeTags($_POST);
            $this->jsonSavaData($type, $values);
        }
    }


    /**
     * Delete action.
     */
    public function deleteAction()
    {
        $values = $this->getRequest()->getPost();
        $getTitle = 'getTitle';
        switch ($values['type']) {
            case 'training':
                $mapper = new Application_Model_TrainingMapper();
                break;
            case 'education':
                $mapper = new Application_Model_EducationMapper();
                break;
            case 'career':
                $mapper = new Application_Model_CareerMapper();
                break;
            case 'publication':
                $mapper = new Application_Model_PublicationMapper();
                break;
            case 'talk':
                $mapper = new Application_Model_TalkMapper();
                break;
            case 'customer':
                $mapper = new Application_Model_CustomerMapper();
                $getTitle = 'getName';
                break;
            case 'certification':
                $mapper = new Application_Model_CertificationMapper();
                $getTitle = 'getName';
                break;
            case 'project':
                $mapper = new Application_Model_ProjectMapper();
                $getTitle = 'getName';
                break;
        }

        $idList = explode(', ', $values['idList']);
        if ($idList && $mapper) {
            foreach ($idList as $id) {
                if ($id) {
                    $object = $mapper->findById($id);
                    $title = $object->$getTitle();
                    if ($object) {
                        $mapper->delete($object);
                        $this->addMessage(ucfirst($values['type']) .' <b>'. $title .'</b> has been deleted.');
                    }
                    else {
                        $this->addError('Error.');
                    }
                }
            }
        }
        $this->printMessages();
    }


    /**
     * Userload action.
     */
    public function langloadAction()
    {
        $jsonData = array('success' => 'true');
        $id = $this->_request->getParam("id");
        $type = $this->_request->getParam("type");

        $datatype = ucfirst($type);
        $classname = 'Application_Model_' . $datatype . 'Mapper';
        $mapper = new $classname;
        $data = $mapper->findById($id);
        $jsonData['data'] = $data->getLanguage(false, true);

        print json_encode($jsonData);
    }

    /**
     * Userload action.
     */
    public function userloadAction()
    {
        $out = '';
        $jsonData = array();

        $userMapper = new Application_Model_UserMapper();
        $uid = $this->_request->getParam("uid");
        $type = $this->_request->getParam("type");
        $user = $userMapper->findByUser($uid);

        if (!$this->acl->can('show__cv', $uid)) {
            return false;
        }

            try {
            $data = $user->load($type);
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
//        $data = $user->load($type);
        if ($data && $type != 'skillCategory') {
            foreach ($data as $dataItem) {
                $jsonData[] = $dataItem->getArray();
            }
        }
        if ($type == 'skillCategory') {
            $jsonData = $data;
        }
        
        if (!empty($jsonData)) {
            foreach ($jsonData as &$row) {
                unset($row['language']);
            }
        }
        print json_encode($jsonData);
    }


    /**
     * Get users list.
     */
    public function userlistAction()
    {
        if (!$this->acl->can('show_any_cv')) {
            $this->addMessage('Authorization error.');
            $this->printMessages();
            exit;
        }
        $users = array();
        $skill = $this->_request->getParam("skill");
        $jsonData = array();
        $userMapper = new Application_Model_UserMapper();
        if ($skill) {
            $mapper = new Application_Model_SkillMapper();
            $userskill = $mapper->findUserBySkill($skill);
            if ($userskill) {
                foreach ($userskill as $uid) {
                    $user = $userMapper->findByUser($uid);
                    if ($user) {
                      $users[] = $user;  
                    }
                }
            }
        }
        else {
          $users = $userMapper->fetchAll();  
        }

        foreach ($users as $user) {
            $userData = $user->getArray();
            unset($userData['profile']);
            unset($userData['password']);
            unset($userData['uid']);
            $jsonData[] = $userData;
        }

        print json_encode($jsonData);
    }


    /**
     * User info action.
     */
    public function userinfoAction()
    {
        $userMapper = new Application_Model_UserMapper();
        $uid = $this->_request->getParam("uid");
        $json = $this->_request->getParam("json");
        $user = $userMapper->findByUser($uid);

        // Personal information
        $this->view->user = $user;
        if ($json) {
            $jsonData = array('success' => 'true');
            $jsonData['data'] = $user->getArray();
            unset($jsonData['data']['password'], $jsonData['data']['userLevel'], $jsonData['data']['language']);
            $lang = $user->getLanguage(false, true);
            if ($lang) {
                $jsonData['data'] = $jsonData['data'] + $lang;
            }
            print json_encode($jsonData);
        }
        else {
            print $this->view->render('ajax/personal_information.phtml');
        }
    }


    /**
     * Get customers list.
     */
    public function customerlistAction()
    {
        $type = $this->_request->getParam("type");
        $jsonData = array();
        $customerMapper = new Application_Model_CustomerMapper();
        $customers = $customerMapper->fetchAll();
        foreach ($customers as $customer) {
            $data = $customer->getArray();
            if ($type == 'small') {
                $jsonData[]['name'] = $data['name'];
            }
            else {
                $jsonData[] = $data;
            }
        }
        print json_encode($jsonData);
    }


    public function loginAction()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $app_options = $bootstrap->getOptions();
        
        // Development access, with any login and pass.
        if (!empty($app_options['ldap']['developmentAccess'])) {
            $this->authAdapter->setIdentity('admin')->setCredential(md5('admin'));
            $result = $this->authAdapter->authenticate();

            $auth = Zend_Auth::getInstance();
            if ($result->isValid()) {
                $identity = $this->authAdapter->getResultRowObject();
                $authStorage = $auth->getStorage();
                $authStorage->write($identity);
            }
        }
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->addMessage('');
            $this->printMessages();
        }

        if ($this->getRequest()->isPost()) {
            $username = $this->getRequest()->getPost('login');
            $password = $this->getRequest()->getPost('password');
            $LDAP = (isset($app_options['ldap']['status']))? $app_options['ldap']['status'] : 0;
            $auth = Zend_Auth::getInstance();
            $options = Array (
              'server1' => $app_options['ldap'],
            );
            
            switch ($LDAP) {
                case 1:
                    require_once 'Zend/Auth/Adapter/Ldap.php';
                    $adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);
                    $result = $auth->authenticate($adapter);

                    $account = $adapter->getAccountObject();
                    if ($account) {
                        $password = md5($password .'CVapp');
                        $userMapper = new Application_Model_UserMapper();
                        $userMapper->addLdapUser($account, $password);
                    }
                    else {
                        $messages = $result->getMessages();
                        $this->addError($messages[0]);
                        $this->printMessages();
                        exit;
                    }

                    break;

                case 0:
                    $password = md5($password);
                    break;
            }

            
            

            $this->authAdapter->setIdentity($username)->setCredential($password);
            $result = $this->authAdapter->authenticate();

            $auth = Zend_Auth::getInstance();
            if ($result->isValid()) {
                $identity = $this->authAdapter->getResultRowObject();
                $authStorage = $auth->getStorage();
                $authStorage->write($identity);
                print json_encode(array('success' => true, 'uid' => $identity->uid));
            } else {
                $this->addError('Incorrect username or password');
                $this->printMessages();
            }
        }
    }


    /**
     * Registration
     */
    /*
     public function registrationAction()
     {
     $this->_helper->viewRenderer->setNoRender();
     $this->_helper->layout->disableLayout();
     $this->_helper->viewRenderer->setNoRender();
     $values = $this->getRequest()->getPost();

     if ($this->getRequest()->isPost()) {
     $user = new Application_Model_User();
     $userMapper = new Application_Model_UserMapper();
     $user->setPassword(md5($values['password']));
     $user->setUserLogin($values['login']);
     $uid = $userMapper->save($user);
     if ($uid) {
     $new_user = $userMapper->findById($uid);

     $this->authAdapter->setIdentity($new_user->getUserLogin())->setCredential($new_user->getPassword());
     $result = $this->authAdapter->authenticate();

     $auth = Zend_Auth::getInstance();
     if ($result->isValid()) {
     $identity = $this->authAdapter->getResultRowObject();
     $authStorage = $auth->getStorage();
     $authStorage->write($identity);
     }
     print json_encode(array('success' => true, 'uid' => $uid));
     }
     }
     }
     */


    /********************************************************************
     * Update
     ********************************************************************/
    public function jsonSavaData($type, $or_values)
    {
        $values = array();
        $values['Language'] = array();
        foreach ($or_values as $key => $var) {
            if (strstr($key, ':')) {
                $lang = explode(':', $key);
                $values['Language'][$lang[0]][$lang[1]] = $var;
            }
            else {
                if (strstr(strtolower($key), 'date')) {
                    $var = str_replace('T00:00:00', '', $var);
                }
                $values[ucfirst($key)] = $var;
            }
        }
        $values['Uid'] = $this->_request->getParam("uid");

        if ($type == 'customer') {
            if (!$this->acl->can('edit_customers', $values['Uid'])) {
                return '';
            }
        }
        else {
            if (!$values['Uid'] || !$this->acl->can('edit__cv', $values['Uid'])) {
                return '';
            }
        }

        // Career
        if ($type == 'career') {
            $mapper = new Application_Model_CareerMapper();
            $dataItem = $this->updateCareer($values, $mapper);
        }

        // Training
        if ($type == 'training') {
            $values['Type'] = 'Training';
            $mapper = new Application_Model_TrainingMapper();
            $dataItem = $this->updateStudy($values, $mapper);
        }

        // Education
        if ($type == 'education') {
            $values['Type'] = 'Education';
            $mapper = new Application_Model_EducationMapper();
            $dataItem = $this->updateStudy($values, $mapper);
        }

        // Publication
        if ($type == 'publication') {
            $values['Type'] = 'Publication';
            $mapper = new Application_Model_PublicationMapper();
            $dataItem = $this->updatePublicity($values, $mapper);
        }

        // Talk
        if ($type == 'talk') {
            $values['Type'] = 'Talk';
            $mapper = new Application_Model_TalkMapper();
            $dataItem = $this->updatePublicity($values, $mapper);
        }

        // Customer
        if ($type == 'customer') {
            unset($values['Uid']);
            $mapper = new Application_Model_CustomerMapper();
            $dataItem = $this->updateCustomer($values, $mapper);
        }

        // Profile
        if ($type == 'profile') {
            $mapper = new Application_Model_UserMapper();
            $dataItem = $this->updateUser($values, $mapper);
        }

        // Certification
        if ($type == 'certification') {
            $mapper = new Application_Model_CertificationMapper();
            $dataItem = $this->updateCertification($values, $mapper);
        }

        // Project
        if ($type == 'project') {
            $mapper = new Application_Model_ProjectMapper();
            $dataItem = $this->updateProject($values, $mapper);
        }
    }

    public function updateStudy($values, $mapper)
    {
        $study = new Application_Model_Study($values);
        if (!$study->getId()) {
            $this->addMessage($study->getType() .' <b>'. $study->getTitle() .'</b> has been created.');
        }
        else {
            $this->addMessage($study->getType() .' <b>'. $study->getTitle() .'</b> has been updated.');
        }
        $mapper->save($study);
        $this->printMessages();
    }


    public function updateCareer($values, $mapper)
    {
        $career = new Application_Model_Career($values);
        if (!$career->getId()) {
            $this->addMessage('Career <b>'. $career->getTitle() .'</b> has been created.');
        }
        else {
            $this->addMessage('Career <b>'. $career->getTitle() .'</b> has been updated.');
        }
        $mapper->save($career);
        $this->printMessages();
    }


    public function updatePublicity($values, $mapper)
    {
        $publicity = new Application_Model_Publicity($values);
        if (!$publicity->getId()) {
            $this->addMessage($publicity->getType() .' <b>'. $publicity->getTitle() .'</b> has been created.');
        }
        else {
            $this->addMessage($publicity->getType() .' <b>'. $publicity->getTitle() .'</b> has been updated.');
        }
        $mapper->save($publicity);
        $this->printMessages();
    }


    public function updateCustomer($values, $mapper)
    {
        $customer = new Application_Model_Customer($values);
        if (!$customer->getId()) {
            $this->addMessage('Customer <b>'. $customer->getName() .'</b> has been created.');
        }
        else {
            $this->addMessage('Customer <b>'. $customer->getName() .'</b> has been updated.');
        }
        $mapper->save($customer);
        $this->printMessages();
    }


    public function updateUser($values, $mapper, $message = true)
    {
        $user = $mapper->findById($values['Uid']);
        $values['lang_data'] = $values['Language'];
        $user->__construct($values);
        $mapper->save($user);
        if ($message) {
            $this->addMessage('Profile has been updated.');
            $this->printMessages();
        }
    }




    public function updateCertification($values, $mapper)
    {
        $certification = new Application_Model_Certification($values);
        if (!$certification->getId()) {
            $this->addMessage('Certification <b>'. $certification->getName() .'</b> has been created.');
        }
        else {
            $this->addMessage('Certification <b>'. $certification->getName() .'</b> has been updated.');
        }
        $mapper->save($certification);
        $this->printMessages();
    }

    public function updateProject($values, $mapper)
    {
        $project = new Application_Model_Project($values);
        if (!$project->getId()) {
            $this->addMessage('Project <b>'. $project->getName() .'</b> has been created.');
        }
        else {
            $this->addMessage('Project <b>'. $project->getName() .'</b> has been updated.');
        }
        $mapper->save($project);
        $this->printMessages();
    }
 

    /********************************************************************
     * Link In parser.
     ********************************************************************/
    public function resumeimportAction($resume = array())
    {
        if (!$resume) {
            if (!$_POST['url']) {
                return false;
            }
            $resume = $this->linkinToArray($_POST['url']);
            if (!$resume || (!$resume['career'] && !$resume['publication'] && !$resume['education'] && !$resume['skills'])) {
                $this->addError('No data to import.');
                $this->printMessages();
                exit;
            }
        }
        $uid = $this->_request->getParam("uid");

        /**
         * Profile
         */
        if (isset($_POST['profile'])) {
            $mapper = new Application_Model_UserMapper();
            $values = array(
                'Uid' => $uid,
                'Language' => '',
                'Lang' => $resume['languages'],
                'Location' => $resume['location'],
                'Profile' => $resume['summary'],
            );
            $dataItem = $this->updateUser($values, $mapper, false);
            $this->addMessage('Profile has been updated');
        }

        /**
         * Career
         */
        if (isset($_POST['career'])) {
            $mapper = new Application_Model_CareerMapper();
            $data = $mapper->findByUser($uid);
            if (!empty($data)) {
                foreach ($data as $val) {
                    $mapper->delete($val);
                }
            }
            if (!empty($resume['career'])) {
                foreach ($resume['career'] as $val) {
                    $career = new Application_Model_Career();
                    $career->setUid($uid);
                    $career->setCustomerName($val['org']);
                    $career->setStartDate($val['dtstart']);
                    $career->setEndDate($val['dtend']);
                    $career->setLocation($val['location']);
                    $career->setDescription($val['description']);
                    $mapper->save($career);
                }
            }
            $this->addMessage('Career has been updated (new: '. count($resume['career']) .').');
        }

        /**
         * Publication
         */
        if (isset($_POST['publication'])) {
            $mapper = new Application_Model_PublicationMapper();
            $data = $mapper->findByUser($uid);
            if (!empty($data)) {
                foreach ($data as $val) {
                    $mapper->delete($val);
                }
            }
            if (!empty($resume['publication'])) {
                foreach ($resume['publication'] as $val) {
                    if (empty($val['date'])) {
                        $val['date'] = '0000-00-00';
                    }
                    $publication = new Application_Model_Publicity();
                    $publication->setUid($uid);
                    $publication->setTitle($val['title']);
                    $publication->setLink($val['url']);
                    $publication->setType('Publication');
                    $publication->setDate($val['date']);
                    $publication->setDescription($val['description']);
                    $mapper->save($publication);
                }
            }
            $this->addMessage('Publication has been updated (new: '. count($resume['publication']) .').');
        }

        /**
         * Education
         */
        if (isset($_POST['education'])) {
            $mapper = new Application_Model_EducationMapper();
            $data = $mapper->findByUser($uid);
            if (!empty($data)) {
                foreach ($data as $val) {
                    $mapper->delete($val);
                }
            }
            if (!empty($resume['education'])) {
                foreach ($resume['education'] as $val) {
                    $studyItem = new Application_Model_Study();
                    $studyItem->setUid($uid);
                    $studyItem->setAchievement($val['org']);
                    $studyItem->setType('Education');
                    $studyItem->setStartDate($val['dtstart']);
                    $studyItem->setEndDate($val['dtend']);
                    $studyItem->setLocation($val['location']);
                    $studyItem->setDescription($val['description']);
                    $mapper->save($studyItem);
                }
            }
            $this->addMessage('Education has been updated (new: '. count($resume['education']) .').');
        }
        
        /**
         * Certification
         */
        if (isset($_POST['certification'])) {
            $mapper = new Application_Model_CertificationMapper();
            $data = $mapper->findByUser($uid);
            if (!empty($data)) {
                foreach ($data as $val) {
                    $mapper->delete($val);
                }
            }
            if (!empty($resume['certification'])) {
                foreach ($resume['certification'] as $val) {
                    if (empty($val['date'])) {
                        $val['date'] = '0000-00-00';
                    }
                    $certification = new Application_Model_Certification();
                    $certification->setUid($uid);
                    $certification->setName($val['name']);
                    $certification->setAuthority($val['authority']);
                    $certification->setNumber($val['number']);
                    $certification->setStartDate($val['dtstart']);
                    $certification->setEndDate($val['dtend']);
                    $mapper->save($certification);
                }
            }
            $this->addMessage('Certification has been updated (new: '. count($resume['certification']) .').');
        }

        /**
         * Project
         */
        if (isset($_POST['project'])) {
            $mapper = new Application_Model_ProjectMapper();
            $data = $mapper->findByUser($uid);
            if (!empty($data)) {
                foreach ($data as $val) {
                    $mapper->delete($val);
                }
            }
            if (!empty($resume['project'])) {
                foreach ($resume['project'] as $val) {
                    if (empty($val['date'])) {
                        $val['date'] = '0000-00-00';
                    }
                    $project = new Application_Model_Project();
                    $project->setUid($uid);
                    $project->setName($val['name']);
                    $project->setOccupation($val['occupation']);
                    $project->setUrl($val['url']);
                    $project->setDescription($val['description']);
                    $project->setStartDate($val['dtstart']);
                    $project->setEndDate($val['dtend']);
                    $mapper->save($project);
                }
            }
            $this->addMessage('Project has been updated (new: '. count($resume['certification']) .').');
        }
        
        
        
        
        
        
        
        
        
        
        
        
        

        $out_skills = array();
        $out_skills_cat = array();
        if (isset($_POST['skills'])) {
            $mapper = new Application_Model_SkillMapper();
            //$mapper->save($uid, array());

            $out_skills_cat = array(array('cat' => '- skip -'));
            $skillCategoryMapper = new Application_Model_SkillCategoryMapper();
            $allSkill = $skillCategoryMapper->getAllSkill();
            if (!empty($allSkill) && !empty($resume['skills'])) {
                foreach ($allSkill as $kat => $data) {
                    $out_skills_cat[] = array('cat' => $kat);
                    if (!empty($data)){
                        foreach ($data as $skill_item) {
                            if (!empty($resume['skills'][$skill_item['name']])) {
                                $out_skills[] = array('cat' => $kat, 'skill' => $skill_item['name']);
                                unset($resume['skills'][$skill_item['name']]);
                            }
                        }
                    }
                }
                if ($resume['skills']) {
                    foreach ($resume['skills'] as $data) {
                        $out_skills[] = array('cat' => '- skip -', 'skill' => $data);
                    }
                }
            }
        }
        $messages = $this->printMessages(false);
        $messages['skills']['data'] = $out_skills;
        $messages['skills']['cats'] = $out_skills_cat;
        print json_encode($messages);
    }

    /**
     * Linkedin parser.
     */
    public function linkinToArray($url)
    {
        require_once(APPLICATION_PATH . '/../library/phpQuery/phpQuery/phpQuery.php');
        $out = array();

        $page = @file_get_contents($url);
        if (!$page) {
            $this->addError('Bad url.');
            $this->printMessages();
            exit;
        }

        $doc = phpQuery::newDocument($page);
        pq('script')->remove();
        $out['location'] = pq('.vcard .locality')->html();
        $out['summary'] = pq('#profile-summary .description')->html();

        /**
         * Career
         */
        $out['career'] = array();
        foreach(pq('.experience') as $li) {
            $experience = array();
            $experience['title'] = pq($li)->find('.title')->html();
            $experience['org'] = pq($li)->find('.org')->html();
            $experience['dtstart'] = pq($li)->find('.dtstart')->attr('title');
            $experience['dtend'] = pq($li)->find('.dtend')->attr('title');
            $experience['location'] = pq($li)->find('.location')->html();
            $experience['description'] = pq($li)->find('.description')->html();
            $out['career'][] = $experience;
        }

        /**
         * Education
         */
        $out['education'] = array();
        foreach(pq('.education') as $li) {
            $education = array();
            $education['org'] = pq($li)->find('.org')->html();
            $education['dtstart'] = pq($li)->find('.dtstart')->attr('title');
            $education['dtend'] = pq($li)->find('.dtend')->attr('title');
            $education['location'] = pq($li)->find('.details-education')->html();
            $education['description'] = pq($li)->find('.desc')->html();
            $out['education'][] = $education;
        }
        $out = $this->cleanText($out);

        /**
         * Publication
         */
        $out['publication'] = array();
        foreach(pq('.publication') as $li) {
            $publication = array();
            $publication['title'] = pq($li)->find('cite')->html();
            $publication['url'] = pq($li)->find('.url')->attr('href');
            $publication['description'] = pq($li)->find('.summary')->html();
            $out['publication'][] = $publication;
        }

        /**
         * Education
         */
        $out['skills'] = array();
        foreach(pq('#skills-list .jellybean') as $li) {
            $out['skills'][] = pq($li)->html();
        }
        $out = $this->cleanText($out);


        /**
         * Languages
         */
        $out['languages'] = array();
        foreach(pq('.languages h3') as $li) {
            $out['languages'][] = pq($li)->html();
        }
        $out['languages'] = implode(', ', $out['languages']);

        $out = $this->cleanText($out);

        if ($out['skills']) {
            $skills_new = array();
            foreach ($out['skills'] as $data) {
                $skills_new[$data] = $data;
            }
            $out['skills'] = $skills_new;
        }

        return $out;

    }

    public function cleanText($array) {
        if ($array) {
            foreach ($array as $key => $data) {
                if (is_string($data)) {
                    $data = str_replace(array("\r", "\n"), ' ', $data);
                    $data = preg_replace('/\s+/', ' ', $data);
                    $data = htmlspecialchars_decode($data);
                    if ($key == 'description' || $key == 'summary') {
                        $data = str_replace(array('<br>', '<br />'), "\r\n", $data);
                    }
                    if ($key == 'url') {
                        $data = str_replace('/redirect?url=', '', $data);
                        $data = urldecode($data);
                    }
                    $array[$key] = trim(strip_tags($data));
                }
                elseif (is_array($data)) {
                    $array[$key] = $this->cleanText($data);
                }
            }
        }
        return $array;
    }



    /**
     * LinkedIn API
     */
    public function linkedinInit() {
        require_once(APPLICATION_PATH . '/../library/linkedin/linkedin_3.1.1.class.php');
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $app_options = $bootstrap->getOptions();
        
        $uid = $this->_request->getParam("uid");
        $API_CONFIG = $app_options['linkedin'];
        
        if(!empty($_SERVER['HTTPS']) &&  $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        $API_CONFIG['callbackUrl'] = $app_options['linkedin']['callback']['protocol'] . '://' . $app_options['linkedin']['callback']['hostname'] . ':' . $app_options['linkedin']['callback']['port'] . '/ajax/linkedin/response/1/uid/'. $uid;
        $this->OBJ_linkedin = new LinkedIn($API_CONFIG);
        if (!empty($_SESSION['oauth']['linkedin']['access'])) {
            $this->OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
        }
    }

    /**
     * Import by LinkedIn API.
     */
    public function linkedinimportAction() {
        $this->linkedinInit();
        if (!empty($_SESSION['oauth']['linkedin']['access'])) {
            $response = $this->OBJ_linkedin->profile('~:(id,first-name,projects:(name,occupation,url,start-date,end-date,description),languages,skills,certifications:(name,authority,number,start-date,end-date),last-name,headline,location,summary,specialties,positions,educations,picture-url,member-url-resources,publications:(id,title,publisher,authors,date,url,summary))');
            if($response['success'] === TRUE) {
                $response['linkedin'] = new SimpleXMLElement($response['linkedin']);

                $out['location'] = (string)$response['linkedin']->location->name;
                $out['summary'] = (string)$response['linkedin']->summary;

                /**
                 * Career
                 */
                $out['career'] = array();
                if (!empty($response['linkedin']->positions->position)) {
                    foreach ($response['linkedin']->positions->position as $data) {
                        $experience = array();
                        $experience['title'] = (string)$data->title;
                        $experience['org'] = (string)$data->company->name;
                        $experience['dtstart'] = '0000-00-00';
                        $experience['dtend'] = '';
                        if (!empty($data->{'start-date'}->year)) {
                            if (!isset($data->date->month)) {
                                $data->{'start-date'}->month = 01;
                            }
                            $experience['dtstart'] = date('Y-m-d', strtotime((string)$data->{'start-date'}->year .'-'.(string)$data->{'start-date'}->month.'-01'));
                        }
                        if (!empty($data->{'end-date'}->year)) {
                            if (!isset($data->date->month)) {
                                $data->{'end-date'}->month = 01;
                            }
                            $experience['dtend'] = date('Y-m-d', strtotime((string)$data->{'end-date'}->year .'-'.(string)$data->{'end-date'}->month.'-01'));
                        }
                        $experience['location'] = '';
                        $experience['description'] = (string)$data->summary;
                        $out['career'][] = $experience;
                    }
                }

                /**
                 * Education
                 */
                $out['education'] = array();
                if (!empty($response['linkedin']->educations->education)) {
                    foreach ($response['linkedin']->educations->education as $data) {
                        $education = array();
                        $education['org'] = (string)$data->activities;
                        $education['dtstart'] = '0000-00-00';
                        $education['dtend'] = '';
                        if ($data->{'start-date'}->year) {
                            $education['dtstart'] = (string)$data->{'start-date'}->year .'-01-01';
                        }
                        if ($data->{'end-date'}->year) {
                            $education['dtend'] = (string)$data->{'end-date'}->year .'-01-01';
                        }
                        $education['location'] = (string)$data->{'school-name'};
                        $education['description'] = (string)$data->notes;
                        $out['education'][] = $education;
                    }
                }

                /**
                 * Publication
                 */
                $out['publication'] = array();
                if (!empty($response['linkedin']->publications->publication)) {
                    foreach ($response['linkedin']->publications->publication as $data) {
                        $publication = array();
                        $publication['title'] = (string)$data->title;
                        $publication['url'] = (string)$data->url;
                        $publication['description'] = (string)$data->summary;
                        if (!empty($data->date->year)) {
                            if (!isset($data->date->month)) {
                                $data->date->month = 01;
                            }
                            if (!isset($data->date->day)) {
                                $data->date->day = 01;
                            }
                            $publication['date'] = date('Y-m-d', strtotime((string)$data->date->year .'-'.(string)$data->date->month.'-'.(string)$data->date->day));
                        }
                        $out['publication'][] = $publication;
                    }
                }

                /**
                 * Certification
                 */
                $out['certification'] = array();
                if (!empty($response['linkedin']->certifications->certification)) {
                        foreach ($response['linkedin']->certifications->certification as $data) {
                            $certification = array();
                            $certification['name'] = (string)$data->name;
                            $certification['authority'] = (string)$data->authority->name;
                            $certification['number'] = (string)$data->number;

                            $certification['dtstart'] = '0000-00-00';
                            $certification['dtend'] = '';
                            if (!empty($data->{'start-date'}->year)) {
                                if (!isset($data->{'start-date'}->month)) {
                                    $data->{'start-date'}->month = 01;
                                }
                                $certification['dtstart'] = date('Y-m-d', strtotime((string)$data->{'start-date'}->year .'-'.(string)$data->{'start-date'}->month.'-01'));
                            }
                            if (!empty($data->{'end-date'}->year)) {
                                if (!isset($data->{'end-date'}->month)) {
                                    $data->{'end-date'}->month = 01;
                                }
                                $certification['dtend'] = date('Y-m-d', strtotime((string)$data->{'end-date'}->year .'-'.(string)$data->{'end-date'}->month.'-01'));
                            }
                            $out['certification'][] = $certification;
                        }
                    }

                /**
                 * Project
                 */
                $out['project'] = array();
                if (!empty($response['linkedin']->projects->project)) {
                    foreach ($response['linkedin']->projects->project as $data) {
                        $project = array();
                        $project['name'] = (string)$data->name;
                        $project['occupation'] = (string)$data->occupation->position->company->name;
                        $project['url'] = (string)$data->url;
                        
                        $project['dtstart'] = '0000-00-00';
                        $project['dtend'] = '';
                        if (!empty($data->{'start-date'}->year)) {
                            if (!isset($data->{'start-date'}->month)) {
                                $data->{'start-date'}->month = 01;
                            }
                            $project['dtstart'] = date('Y-m-d', strtotime((string)$data->{'start-date'}->year .'-'.(string)$data->{'start-date'}->month.'-01'));
                        }
                        if (!empty($data->{'end-date'}->year)) {
                            if (!isset($data->{'end-date'}->month)) {
                                $data->{'end-date'}->month = 01;
                            }
                            $project['dtend'] = date('Y-m-d', strtotime((string)$data->{'end-date'}->year .'-'.(string)$data->{'end-date'}->month.'-01'));
                        }
                        $project['description'] = (string)$data->description;
                        $out['project'][] = $project;
                    }
                }

                /**
                 * Languages
                 */
                $out['languages'] = array();
                if (!empty($response['linkedin']->languages->language)) {
                    foreach ($response['linkedin']->languages->language as $language) {
                        $out['languages'][] = (string)$language->language->name;
                    }
                }
                $out['languages'] = implode(', ', $out['languages']);

                /**
                 * Skills.
                 */
                $out['skills'] = array();
                if (!empty($response['linkedin']->skills->skill)) {
                    foreach ($response['linkedin']->skills->skill as $skill) {
                        $out['skills'][(string)$skill->skill->name] = (string)$skill->skill->name;
                    }
                }

                $this->resumeimportAction($out);
            }
            else {
                $json = json_encode(array('success' => 'false'));
            }
        }
    }


    public function linkedinAction() {
        $is_response = $this->_request->getParam("response");
        $uid = $this->_request->getParam("uid");
        $this->linkedinInit();

        // check for response from LinkedIn
        if (empty($_SESSION['oauth']['linkedin']['access'])) {
            if(!$is_response) {
                $response = $this->OBJ_linkedin->retrieveTokenRequest();
                if($response['success'] === TRUE) {
                    $_SESSION['oauth']['linkedin']['request'] = $response['linkedin'];
                    header('Location: ' . LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
                } else {
                    echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($this->OBJ_linkedin, TRUE) . "</pre>";
                }
            } elseif (!empty($_GET['oauth_verifier'])) {
                $response = $this->OBJ_linkedin->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
                if($response['success'] === TRUE) {
                    $_SESSION['oauth']['linkedin']['access'] = $response['linkedin'];
                    $_SESSION['oauth']['linkedin']['authorized'] = TRUE;
                } else {
                    echo "Access token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($this->OBJ_linkedin, TRUE) . "</pre>";
                }
            }
        }
        if (!empty($_SESSION['oauth']['linkedin']['access'])) {
            //$response = $this->OBJ_linkedin->profile('~:(id,first-name,last-name)');
            $response = $this->OBJ_linkedin->profile('~:(id,first-name,last-name,headline,location,summary,specialties,positions,educations,picture-url,member-url-resources,publications:(id,title,publisher,authors,date,url,summary))');

            if($response['success'] === TRUE) {
                $response['linkedin'] = new SimpleXMLElement($response['linkedin']);
                $out = array('success' => 'true');
                $out['linkedin'] = $response['linkedin'];
                $json = json_encode($out);
            }
            else {
                $json = json_encode(array('success' => 'false'));
            }
            print '<script>window.opener.linkedInStatusUpd('. $json .');self.close()</script>';
        }
    }


    /********************************************************************
     * Messages
     ********************************************************************/
    public function addError($text)
    {

        $this->_error[] = $text;
    }


    public function addMessage($text)
    {
        $this->_messages[] = $text;
    }


    public function printMessages($print = true)
    {
        $out = array();
        $out['success'] = 'true';
        $out['messages'] = 'false';
        $messages = array();
        if ($this->_messages) {
            foreach ($this->_messages as $message) {
                $msg = array();
                $msg['body'] = $message;
                $msg['title'] = '';
                $msg['sticky'] = 'false';
                $msg['theme'] = '';
                $messages[] = $msg;
            }
            $this->_messages = array();
        }
        if ($this->_error) {
            unset($out['success']);
            foreach ($this->_error as $message) {
                $msg = array();
                $msg['body'] = $message;
                $msg['title'] = 'Error';
                $msg['sticky'] = 'true';
                $msg['theme'] = 'red_theme';
                $messages[] = $msg;
            }
            $this->_error = array();
        }
        if ($messages) {
            $out['messages'] = $messages;
        }
        if ($print) {
            print json_encode($out);
        }
        else {
            return $out;
        }
    }

    public function removeTags($array) {
        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $data) {
                $array[$key] = $this->removeTags($data);
            }
            return $array;
        }
        elseif (is_string($array)) {
            return strip_tags($array);
        }
        else {
            return $array;
        }
    }
}

