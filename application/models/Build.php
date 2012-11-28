<?php

class Application_Model_Build extends Application_Model_BaseInit
{

    protected $_id;
    protected $_uid;
    protected $_lang;
    protected $_title;
    protected $_created;
    protected $_changed;
    protected $_resume_data = array();
    protected $_user;

    protected $_fieldsCareer = array('id', 'customerName', 'startDate', 'endDate', 'function', 'location', 'description');
    protected $_fieldsStudy = array('id', 'title', 'startDate', 'endDate', 'location', 'description');
    protected $_fieldsPublicity = array('id', 'title', 'date', 'link', 'description');
    protected $_fieldsCertification = array('id', 'name', 'startDate', 'endDate', 'authority', 'number');
    protected $_fieldsProject = array('id', 'name', 'startDate', 'endDate', 'occupation', 'url', 'description');


    public function init($row)
    {
        $this->setId($row->id);
        $this->setUid($row->uid);
        $this->setTitle($row->title);
        $this->setCreated($row->created);
        $this->setLang($row->lang);
        $this->setChanged($row->changed);
        $this->setData(unserialize($row->resume_data));
        $this->updateSkill($row->uid);
    }

    public function setId($number)
    {
        $this->_id = (int) $number;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setUid($number)
    {
        $userMapper = new Application_Model_UserMapper();

        $this->_uid = (int) $number;
        $this->_user = $userMapper->findByUser($this->_uid);
        return $this;
    }

    public function getUid()
    {
        return $this->_uid;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function setCreated($date)
    {
        $this->_created = $date;
        return $this;
    }

    public function getCreated()
    {
        return $this->_created;
    }

    public function setLang($text)
    {
        $this->_lang = $text;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($text)
    {
        $this->_title = $text;
        return $this;
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function setChanged($text)
    {
        $this->_changed = $text;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setData($data, $type = '', $key = '')
    {
        if ($key) {
            $this->_resume_data[$type][$key] = $data;
        }
        elseif ($type) {
            $this->_resume_data[$type] = $data;
        }
        else {
            $this->_resume_data = $data;
        }
        return $this;
    }

    public function getData($type = '', $key = '')
    {
        if ($key) {
            if (!empty($this->_resume_data[$type][$key])) {
                return $this->_resume_data[$type][$key];
            }
        }
        elseif ($type) {
            if (!empty($this->_resume_data[$type])) {
                return $this->_resume_data[$type];
            }
        }
        else {
            return $this->_resume_data;
        }
    }





    public function setInitBuild()
    {
        $this->updateCareer();
        $this->updateTraining();
        $this->updateEducation();
        $this->updatePublication();
        $this->updateTalk();
        $this->updateCertification();
        $this->updateProject();
        $this->updateProfile();
        $this->updateBlocks();
    }

    public function save()
    {
        $this->setChanged(date('Y-m-d'));
        $buildMapper = new Application_Model_BuildMapper();
        $id = $buildMapper->save($this);
        $this->setId($id);
        return $id;
    }


    public function updateData($type, $fields = array(), $add_fields = array())
    {
        $out = array();
        $userData = $this->_user->load($type);

        foreach ($userData as $dataItem) {
            if ($dataItem->initLanguage($this->getLang())) {
                $dataArr = $dataItem->getArray();
                $id = 0;
                $newArr = array();
                foreach ($dataArr as $key => $val) {
                    if (!$fields || in_array($key, $fields)) {
                        $newArr[$key] = $val;
                    }
                }
                if ($add_fields) {
                    foreach ($add_fields as $key => $var) {
                        $newArr[$key] = $var;
                    }
                }
                $out[$dataArr['id']] = $newArr;
            }
        }

        $this->setData($out, $type);
        return $out;
    }


    public function updateProfile()
    {
        $customerMapper = new Application_Model_UserMapper();
        $user = $customerMapper->findById($this->_uid);
        if ($user->initLanguage($this->getLang())) {
            $profile = $user->getArray();
            unset(
            $profile['uid'], $profile['id'], $profile['userLogin'], $profile['password'],
            $profile['userLevel'], $profile['viewLink'], $profile['editProfileLink']
            );
        }
        else {
            $profile = array();
        }
        $this->setData($profile, 'profile');
        return $profile;
    }

    public function updateBlocks()
    {
        $this->setData(array(
          'career' => true, 
          'education' => true, 
          'publication' => true, 
          'talk' => true, 
          'certification' => true, 
          'project' => true,
          'training' => true
        ), 'blocks');
    }

    public function updateCareer()
    {
        return $this->updateData('career', $this->_fieldsCareer, array('omit_date' => false, 'client_info' => true, 'category' => '- none -'));
    }


    public function updateTraining()
    {
        return $this->updateData('training', $this->_fieldsStudy);
    }


    public function updateEducation()
    {
        return $this->updateData('education', $this->_fieldsStudy);
    }


    public function updatePublication()
    {
        return $this->updateData('publication', $this->_fieldsPublicity);
    }


    public function updateTalk()
    {
        return $this->updateData('talk', $this->_fieldsPublicity);
    }


    public function updateCertification()
    {
        return $this->updateData('certification', $this->_fieldsCertification);
    }


    public function updateProject()
    {
        return $this->updateData('project', $this->_fieldsProject);
    }

    public function updateSkill($uid)
    {
        $mapper = new Application_Model_SkillMapper();
        $userskill = $mapper->findByUser($uid);
        $this->setData($userskill, 'skill');
    }



}

