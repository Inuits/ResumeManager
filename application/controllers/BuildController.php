<?php

class BuildController extends Zend_Controller_Action
{
    protected $authAdapter;

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->acl = new Application_Model_Acl();
        $this->_helper->layout->disableLayout();
        $this->authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $this->authAdapter->setTableName('user')->setIdentityColumn('user_login')->setCredentialColumn('password');
    }
    
    
    public function createAction() {
        $values = $this->removeTags($_POST);
        $name = $values['name'];
        if (!$name) {
            $name = $values['emptyname'];
        }
        $lang = $values['language'];
        $uid = $values['uid'];
        $date = $values['date'];
    
        if (!$this->acl->can('export__cv', $uid)) {
            return false;
        }
        
        $build = new Application_Model_Build();
        $build->setUid($values['uid']);
        $build->setLang($values['language']);
        $build->setTitle($name);
        $build->setCreated(date('Y-m-d'));
        $build->setInitBuild();
        $build->save();

        $jsonData = array('success' => 'true');
        $jsonData['bid'] = $build->getId();
        print json_encode($jsonData);
    }


    public function profileAction() {
        $values = $this->removeTags($_POST);
        $jsonData = array('success' => 'true');
        $id = $this->_request->getParam("id");
        $reload = $this->_request->getParam("reload");

        $buildMapper = new Application_Model_BuildMapper();
        $build = $buildMapper->findById($id);
        if (!$this->acl->can('export__cv', $build->getUid())) {
            return false;
        }
        if (!empty($reload)) {
            $build->updateProfile();
            $build->save();
        }
        $profile = $build->getData('profile', 'profile');
        if (!empty($values['profile'])) {
            $build->setData($values['profile'], 'profile', 'profile');
            $build->save();
        }
        else {
            $jsonData['data']['profile'] = $profile;
        }
        print json_encode($jsonData);
    }


    public function blocksAction() {
        $values = $this->removeTags($_POST);;
        $jsonData = array('success' => 'true');
        $id = $this->_request->getParam("id");
        $reload = $this->_request->getParam("reload");
        $blocks = array(
          'career' => false, 
          'education' => false, 
          'publication' => false, 
          'talk' => false,  
          'certification' => false,  
          'project' => false, 
          'training' => false
        );

        $buildMapper = new Application_Model_BuildMapper();
        $build = $buildMapper->findById($id);
        if (!$this->acl->can('export__cv', $build->getUid())) {
            return false;
        }

        if ($values) {
            foreach ($values as $key => $data) {
                $blocks[$key] = true;
            }
            $build->setData($blocks, 'blocks');
            $build->save();
            print json_encode($jsonData);
        }
        else {
            $blocks = $build->getData('blocks');
            $jsonData['data'] = $blocks;
            print json_encode($jsonData);
        }
    }


    public function gridAction() {
        $jsonData = array();
        $id = $this->_request->getParam("id");
        $type = $this->_request->getParam("type");
        if ($this->_request->getParam("page") == '999') {
            $reload = true;
        }

        $buildMapper = new Application_Model_BuildMapper();
        $build = $buildMapper->findById($id);
        if (!$this->acl->can('export__cv', $build->getUid())) {
            return false;
        }
        if (!empty($reload)) {
            $fn = 'update' . ucfirst($type);
            $build->$fn();
            $build->save();
        }

        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $jsonPost = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
            $jsonPost = $this->removeTags((array) $jsonPost);
            if (!empty($jsonPost['id'])) {
                $build->setData($this->clearDate($jsonPost), $type, $jsonPost['id']);
            }
            elseif (!empty($jsonPost[0]->id)) {
                foreach ($jsonPost as $item) {
                    $item = (array) $item;
                    $build->setData($this->clearDate($item), $type, $item['id']);
                }
            }
            $build->save();
        }
        $data = $build->getData($type);
        if ($data) {
            foreach ($data as $val) {
                $jsonData[] = $val;
            }
        }
        print json_encode($jsonData);
    }


    public function clearDate($values) {
        foreach ($values as $key => $var) {
            if (strstr(strtolower($key), 'date')) {
                $var = str_replace('T00:00:00', '', $var);
            }
            $values[$key] = $var;
        }
        return $values;
    }


    public function buildsAction() {
        $jsonData = array();
        $uid = $this->_request->getParam("uid");
    
        if (!$this->acl->can('export__cv', $uid)) {
            return false;
        }
        
        $buildMapper = new Application_Model_BuildMapper();
        $builds = $buildMapper->getBuilds($uid);
        foreach ($builds as $key => $var) {
            unset($var['resume_data'], $var['uid']);
            $jsonData[] = $var;
        }
        print json_encode($jsonData);
    }


    public function deleteAction() {
        $out = array('success' => 'true');
        $id = $this->_request->getParam("id");
        $buildMapper = new Application_Model_BuildMapper();
        $build = $buildMapper->findById($id);
        if (!$this->acl->can('export__cv', $build->getUid())) {
            return false;
        }
        $buildMapper->delete($build);
        print json_encode($out);
    }


    /********************************************************************
     * Export
     ********************************************************************/

    public function exportAction() {
        $type = $this->_request->getParam("type");
        $id = $this->_request->getParam("id");
        $out = '';

        if ($type == 'pdf') {
            $theme = 'theme_html';
        }
        elseif ($type == 'rtf') {
            $theme = 'theme_rtf';
        }
        else {
            exit();
        }

        $buildMapper = new Application_Model_BuildMapper();
        $build = $buildMapper->findById($id);
        $build_arr = $build->getData();
    
        if (!$this->acl->can('export__cv', $build->getUid())) {
            return false;
        }
        
        $build_arr = $this->addNewLine($build_arr, $theme);
        
        
        // Career.
        $data = $this->get_data('career', $build_arr);
        if ($data) {
            $out .= $this->themeCareer($data, 'CAREER HISTORY', $theme);
        }
        // Training.
        $data = $this->get_data('training', $build_arr);
        if ($data) {
            $out .= $this->themeStudy($data, 'TRAINING', $theme);
        }
        // Training.
        $data = $this->get_data('education', $build_arr);
        if ($data) {
            $out .= $this->themeStudy($data, 'EDUCATION', $theme);
        }
        // Publication.
        $data = $this->get_data('publication', $build_arr);
        if ($data) {
            $out .= $this->themePublicity($data, 'PUBLICATION', $theme);
        }
        // Talk.
        $data = $this->get_data('talk', $build_arr);
        if ($data) {
            $out .= $this->themePublicity($data, 'TALK', $theme);
        }
        // Certification.
        $data = $this->get_data('certification', $build_arr);
        if ($data) {
            $out .= $this->themeCertification($data, 'CERTIFICATION', $theme);
        }
        // Project.
        $data = $this->get_data('project', $build_arr);
        if ($data) {
            $out .= $this->themeProject($data, 'PROJECT', $theme);
        }
        // Skills.
        $out .= $this->themeSkill($build_arr['skill'], 'SKILLS', $theme);

    
        if (empty($build_arr['profile']['lastName'])) {
            $build_arr['profile']['lastName'] = 'LastName';
        }
        if (empty($build_arr['profile']['firstName'])) {
            $build_arr['profile']['firstName'] = 'FirstName';
        }
        if (empty($build_arr['profile']['profile'])) {
            $build_arr['profile']['profile'] = '';
        }
        
        // Export.
        if ($type == 'pdf') {
            $this->exportPdf($out, $build_arr);
        }
        elseif ($type == 'rtf') {
            $this->exportRtf($out, $build_arr);
        }
    }


    public function exportRtf($body, $build_arr) {
        $body = strip_tags($body);
        require_once(APPLICATION_PATH . '/../library/rtfgenclass/class.rtf.php');
        $RTF = new RTF();
        $RTF->set_default_font("Times New Roman", 12);


        $RTF->add_image(APPLICATION_PATH . '/../public/images/inuits.png', 80, "center");
        $RTF->new_line();
        $RTF->add_text('{\\fs34\\b Duboisstraat 50\\b0 }\\line\n', 'center');
        $RTF->add_text('{\\fs34\\b 2060 Antwerp\\b0 }\\line\n\\line', 'center');
        $RTF->new_line();
        $RTF->new_line();
        $RTF->add_text('{\\fs44\\b '. $build_arr['profile']['firstName'] .' '. $build_arr['profile']['lastName'] .'\\b0 }\\line\n\\line', 'center');


        $birth = '';
        if (!empty($build_arr['profile']['birthDate'])) {
            $birth .= $build_arr['profile']['birthDate'];
            if ($build_arr['profile']['birthPlace']) {
                $birth .= ', '. $build_arr['profile']['birthPlace'];
            }
        }
        $build_arr['profile']['birth'] = $birth;
        $table = '';
        $table .= '\par \trowd\trql\ltrrow\trpaddft3\trpaddt0\trpaddfl3\trpaddl0\trpaddfb3\trpaddb0\trpaddfr3\trpaddr0\cellx4986\cellx9972\pard\plain' . "\n";
        $table .= $this->theme_rtf_tr('Location', $build_arr, 'location');
        $table .= $this->theme_rtf_tr('Birth', $build_arr, 'birth');
        $table .= $this->theme_rtf_tr('Social security', $build_arr, 'socialSecurity');
        $table .= $this->theme_rtf_tr('Language', $build_arr, 'lang');
        $table .= $this->theme_rtf_tr('Nationality', $build_arr, 'nationality');
        $table = substr($table, 0, -142);
        $table .= '\cell\row\pard\pard\plain';
        $RTF->add_text($table);
        $RTF->new_line();
        $RTF->new_line();

        $RTF->add_image(APPLICATION_PATH . '/../public/images/inuits-wolf.png', 62, "center");

        $RTF->add_text("\\line\\line{\\fs34\\b Profile:\\b0}\\line{\\fs34{$build_arr['profile']['profile']}}\\line\n", "left");

        $RTF->new_page();

        $body = $this->utf8_to_rtf($body);
        //        print $body;
        $RTF->add_text($body);
        $RTF->display($build_arr['profile']['firstName'] .'_'. $build_arr['profile']['lastName'] .'.rtf');

    }


    function utf8_to_rtf($utf8_text) {
        $utf8_patterns = array(
      "[\xC2-\xDF][\x80-\xBF]",
      "[\xE0-\xEF][\x80-\xBF]{2}",
      "[\xF0-\xF4][\x80-\xBF]{3}",
        );
        $new_str = $utf8_text;
        foreach($utf8_patterns as $pattern) {
            $new_str = preg_replace("/($pattern)/e",
        "'\u'.hexdec(bin2hex(mb_convert_encoding('$1', 'UTF-16', 'UTF-8'))).'?'", 
            $new_str);
        }
        return $new_str;
    }


    public function theme_rtf_tr($title, $build_arr, $key) {
        if (empty($build_arr['profile'][$key])) {
            return '';
        }
        else {
            $val = $build_arr['profile'][$key];
            $n = "\n";
            $title = "{\\fs34 {$title}: }";
            $val = "{\\fs34 {$val} }";

            $out =  ' \s20{\*\hyphen2\hyphlead2\hyphtrail2\hyphmax0}\cf0\kerning1\hich\af0\langfe2052\dbch\af0\loch\f0\fs24\lang1033\intbl\qr{\rtlch \ltrch\loch'. $n .' '. $title .' }\cell\pard\plain'. $n . $n;
            $out .=  ' \s20{\*\hyphen2\hyphlead2\hyphtrail2\hyphmax0}\cf0\kerning1\hich\af0\langfe2052\dbch\af0\loch\f0\fs24\lang1033\intbl{\rtlch \ltrch\loch'. $n .' '. $val .'}\cell\row\pard\trowd\trql\ltrrow\trpaddft3\trpaddt0\trpaddfl3\trpaddl0\trpaddfb3\trpaddb0\trpaddfr3\trpaddr0\cellx4986\cellx9972\pard\plain '. $n . $n;
            return $out;
        }
    }


    public function exportPdf($body, $build_arr) {

        // Header.
        $stylesheet = 'body {font-size: 14px;} .text {padding: 0 40px;}';
        $header = '';
        $header .= '<div class="text" style="text-align: center; font-size: 18px; font-weight: bold; ">';
        $header .= '<img src="/images/inuits.png">';
        $header .= '<br /><br /><span>Duboisstraat 50<br />2060 Antwerp</span>';
        $header .= '<br /><br /><br /><br /><span style="font-size: 26px;">'. $build_arr['profile']['firstName'] .' '. $build_arr['profile']['lastName'] .'</span><br /><br />';
        $header .= '</div>';

        // Info table.
        $header .= '<table class="text" style="text-align: center; font-size: 18px; margin: auto; ">';
        $birth = '';
        if (!empty($build_arr['profile']['birthDate'])) {
            $birth .= $build_arr['profile']['birthDate'];
            if ($build_arr['profile']['birthPlace']) {
                $birth .= ', '. $build_arr['profile']['birthPlace'];
            }
        }
        $build_arr['profile']['birth'] = $birth;
        $header .= $this->theme_html_tr('Location', $build_arr, 'location');
        $header .= $this->theme_html_tr('Birth', $build_arr, 'birth');
        $header .= $this->theme_html_tr('Social security', $build_arr, 'socialSecurity');
        $header .= $this->theme_html_tr('Language', $build_arr, 'lang');
        $header .= $this->theme_html_tr('Nationality', $build_arr, 'nationality');
        $header .= '</table><br /><br />';

        // Image.
        $header .= "<div style=\"text-align: center;\"><img src=\"/images/inuits-wolf.png\"></div>";

        // Profile.
        if ($build_arr['profile']['profile']) {
            $header .= '<div style="font-size: 18px;"  class="text">';
            $header .= '<br /><br /><strong>'. 'Profile' .'</strong><br />';
            $header .= $build_arr['profile']['profile'];
            $header .= '<div>';
        }
        $header .= '<pagebreak>';


        $out = $header . '<div  class="text">' . $body . '</div>';

        require_once(APPLICATION_PATH . '/../library/MPDF53/mpdf.php');
        $mpdf = new mPDF('utf-8', 'A4', '8', '', 0, 0, 20, 20, 10, 10);
        $mpdf->charset_in = 'utf8';

        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($out, 2);
        $mpdf->Output($build_arr['profile']['firstName'] .'_'. $build_arr['profile']['lastName'] .'.pdf', 'I');
    }


    public function theme_html_tr($title, $build_arr, $key) {
        if (empty($build_arr['profile'][$key])) {
            return '';
        }
        else {
            $val = $build_arr['profile'][$key];
            return '<tr><td style="text-align: right; width: 300px;">'. $title .': </td><td style="text-align: left; width: 300px;">'. $val .'</td></tr>';
        }
    }


    public function get_data($key, $build) {
        if ($build[$key] && $build['blocks'][$key]) {
            return $build[$key];
        }
        else {
            return false;
        }
    }


    public function theme_date($date) {
        return date('d M Y', strtotime($date));
    }


    public function theme_date_range($item, $present = TRUE) {
        $out = '';
        if (empty($item['omit_date'])){
            if ($item['startDate']) {
                $out .= $this->theme_date($item['startDate']);
            }
            else {
                return '';
            }

            if ($item['endDate'] != $item['startDate']) {
                if ($item['endDate']) {
                    $out .= ' - ';
                    $out .= $this->theme_date($item['endDate']);
                }
                elseif ($present) {
                    $out .= ' - ';
                    $out .= 'Present';
                }
            }
        }
        return $out;
    }

    public function theme_html($tag, $text = '') {
        if (!$text && $tag != 'br') {
            return '';
        }
        switch ($tag) {
            case 'br':
                return "<br />";
                break;

            case 'p':
                return "<br />{$text}<br />";
                break;

            case 'title':
                return "<br /><h3 style=\"line-height: 0; height: 0; margin: 0; padding: 0; padding-bottom: 2px; border-bottom-style: solid; border-bottom-color: #444; border-bottom-width: 1px;\">{$text}</h3>";
                break;

            case 'subtitle':
                $subtitle = array();
                if ($text) {
                    foreach ($text as $key => $data) {
                        $subtitle[] = "$key: $data";
                    }
                }
                $text =  implode('<br />', $subtitle);
                $text = '<span style="color: #444;">'. $text .'</span>';
                return $text;
                break;

            case 'center':
                return "<div align=\"center\">{$text}</div>";
                break;

            case 'a':
                return "<a href=\"{$text}\">{$text}</a>";
                break;

            default:
                return "<{$tag}>{$text}</{$tag}>";
                break;
        }
    }


    public function theme_rtf($tag, $text = '') {
        if (!$text && $tag != 'br') {
            return '';
        }
        switch ($tag) {
            case 'br':
                return "\\line\n ";
                break;

            case 'p':
                return "\\line\n{$text}\\line\n";
                break;

            case 'strong':
                return "\\b {$text}\\b0 ";
                break;

            case 'title':
                return '\par \trowd\trql\ltrrow\trpaddft3\trpaddt0\trpaddfl3\trpaddl0\trpaddfb3\trpaddb0\trpaddfr3\trpaddr0\clbrdrb\brdrhair\brdrcf1\cellx9972\pard\plain \s0\nowidctlpar{\*\hyphen2\hyphlead2\hyphtrail2\hyphmax0}\cf0\kerning1\hich\af0\langfe2052\dbch\af0\afs24\lang1081\loch\f0\fs24\lang1033\intbl{\b\hich\af0\dbch\af0\afs30\ab\rtlch \ltrch\loch\fs30
 '. $text .'}\cell\row\pard\pard\plain';
                break;

            case 'subtitle':
                $subtitle = array();
                if ($text) {
                    foreach ($text as $key => $data) {
                        $subtitle[] = '{\cf15 '. $key .': '. $data .'}';
                    }
                }
                return implode("\\line\n ", $subtitle);
                break;

            case 'a':
                return "{$text}";
                break;

            default:
                return "{$text}";
                break;
        }
    }


    public function themePublicity($data, $title, $theme) {
        $out = '';
        $out .= $this->$theme('title', $title);
        foreach ($data as $item) {
            $item_text = '';
            $subtitle = array();
            $item_text .= $this->$theme('strong', $item['title']);
            $item_text .= $this->$theme('br');

            if ($item['link']) {
                $subtitle['Url'] = $this->$theme('a', $item['link']);
            }
            if ($item['date']) {
                $subtitle['Date'] = $this->theme_date($item['date']);
            }
            $item_text .= $this->$theme('subtitle', $subtitle);

            if ($item['description']) {
                $item_text .= $this->$theme('br');
                $item_text .= $item['description'];
            }
            $out .= $this->$theme('p', $item_text);
        }
        $out .= $this->$theme('br');
        return $out;
    }


    public function themeCertification($data, $title, $theme) {
        $out = '';
        $out .= $this->$theme('title', $title);
        foreach ($data as $item) {
            $subtitle = array();
            $item_text = '';

            if ($item['name']) {
                $item_text .= $this->$theme('strong', $item['name']);
                $item_text .= $this->$theme('br');
            }

            if ($item['authority']) {
                $subtitle['Certification Authority'] = $item['authority'];
            }
            if ($item['number']) {
                $subtitle['License Number'] = $item['number'];
            }
            if ($date = $this->theme_date_range($item, FALSE)) {
                $subtitle['Date'] = $date;
            }
            $item_text .= $this->$theme('subtitle', $subtitle);

            $out .= $this->$theme('p', $item_text);
        }
        $out .= $this->$theme('br');
        return $out;
    }


    public function themeProject($data, $title, $theme) {
        $out = '';
        $out .= $this->$theme('title', $title);
        foreach ($data as $item) {
            $subtitle = array();
            $item_text = '';

            if ($item['name']) {
                $item_text .= $this->$theme('strong', $item['name']);
                if ($item['occupation']) {
                    $item_text .= $this->$theme('strong', ' ('. $item['occupation'] .')');
                }
                $item_text .= $this->$theme('br');
            }

            if ($item['url']) {
                $subtitle['Url'] = $this->$theme('a', $item['url']);
            }
            if ($date = $this->theme_date_range($item)) {
                $subtitle['Date'] = $date;
            }
            $item_text .= $this->$theme('subtitle', $subtitle);


            if ($item['description']) {
                $item_text .= $this->$theme('br');
                $item_text .= $item['description'];
            }
            $out .= $this->$theme('p', $item_text);
        }
        $out .= $this->$theme('br');
        return $out;
    }
    

    public function addNewLine($array, $theme) {
        
        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $data) {
                $array[$key] = $this->addNewLine($data, $theme);
            }
            return $array;
        }
        elseif (is_string($array)) {
            return str_replace("\n", $this->$theme('br'), $array);
        }
        else {
            return $array;
        }
    }
    
    
    public function themeStudy($data, $title, $theme) {
        $out = '';
        $out .= $this->$theme('title', $title);
        foreach ($data as $item) {
            $subtitle = array();
            $item_text = '';

            if ($item['title']) {
                $item_text .= $this->$theme('strong', $item['title']);
                $item_text .= $this->$theme('br');
            }

            if ($item['location']) {
                $subtitle['Location'] = $item['location'];
            }
            if ($date = $this->theme_date_range($item)) {
                $subtitle['Date'] = $date;
            }
            $item_text .= $this->$theme('subtitle', $subtitle);


            if ($item['description']) {
                $item_text .= $this->$theme('br');
                $item_text .= $item['description'];
            }
            $out .= $this->$theme('p', $item_text);
        }
        $out .= $this->$theme('br');
        return $out;
    }


    public function themeCareer($data, $title, $theme) {
        $out = '';
        $career_arr = array();
        foreach ($data as $item) {
            $item_text = '';
            $subtitle = array();

            if ($item['client_info']) {
                $item_text .= $this->$theme('strong', $item['customerName']);
                $item_text .= $this->$theme('br');
            }

            if ($item['function']) {
                $subtitle['Function'] = $item['function'];
            }
            if ($item['location']) {
                $subtitle['Location'] = $item['location'];
            }
            if ($date = $this->theme_date_range($item)) {
                $subtitle['Date'] = $date;
            }
            $item_text .= $this->$theme('subtitle', $subtitle);


            if ($item['description']) {
                $item_text .= $this->$theme('br');
                $item_text .= $item['description'];
            }

            if (!$item['category']) {
                $item['category'] = 0;
            }
            $career_arr[$item['category']][] = $item_text;
        }

        foreach ($career_arr as $category => $data) {
            $cat_title = $cat_text = '';
            foreach ($data as $item) {
                $cat_text .= $this->$theme('p', $item);
            }
            if (trim($category) && $category != '- none -' && $cat_text) {
                $cat_title .= $this->$theme('br');
                $cat_title .= $this->$theme('br');
                $cat_title .= $this->$theme('strong', strtoupper("$category"));
                $cat_title .= $this->$theme('br');
                $out .= $cat_title . $cat_text;
            }
            else {
                $out = $cat_title . $cat_text . $out;
            }
        }
        $out = $this->$theme('title', $title) . $out;
        $out .= $this->$theme('br');
        return $out;
    }


    public function themeSkill($data, $title, $theme) {
        $out = '';
        foreach ($data as $key => $data) {
            $item_text = '';
            foreach ($data as $skillInfo) {
                if ($skillInfo['act']) {
                    if ($item_text) {
                        $item_text .= ', ';
                    }
                    $item_text .= $skillInfo['name'];
                }
            }
            if ($item_text) {
                $out .= $this->$theme('strong', $key .': ');
                $out .= $item_text . '.';
                $out .= $this->$theme('br');
            }
        }
        if ($out) {
            $out = $this->$theme('title', $title) . $this->$theme('p', $out);
        }
        return $out;
    }



    public function removeTags($array) {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
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
