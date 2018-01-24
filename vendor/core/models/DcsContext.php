<?php
namespace Dcs\Vendor\Core\Models;

//use dcs\vendor\core\Common_data;
//use dcs\vendor\core\User;
//use dcs\vendor\core\CollectionSet;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class DcsContext 
{
    protected $context;
    protected $valindx = array (
                            array('name' => "ROOT", 'validate'=>FALSE),
                            array('name' => "PREFIX", 'validate'=>FALSE),
                            array('name' => "MODE", 'validate'=>FALSE),
                            array('name' => "ITEMID", 'validate'=>TRUE),
                            array('name' => "CURID", 'validate'=>TRUE),
                            array('name' => "ACTION", 'validate'=>FALSE),
                            array('name' => "PARAM", 'validate'=>FALSE),
                        );
    
    function __construct() {
        $this->context = array();
        $this->context['TITLE'] = DCS_COMPANY_SHORTNAME;
        $this->context['MODE'] = 'ENTERPRISE';
        $this->context['CLASSNAME'] = '';
        $this->context['CLASSTYPE'] = '';
        $this->context['ACTION'] = 'VIEW';
        $this->context['PREFIX'] = '';
        $this->context['ITEMID'] = '';
        $this->context['CURID'] = '';
        $this->context['SETID'] = '';
        $this->context['PAGE'] = 1;
        $this->context['LIMIT'] = DCS_COUNT_REC_BY_PAGE;
        $this->context['MENU'] = array();
        $this->context['USERNAME'] = 'Anonymous';
        $this->context['DATA'] = array();
    } 
    
    public function getcontext()
    {
        return $this->context;
    }   
    //
    // $data = array 
    // [0] - document_root
    // [1] - prefix (is empty or <config>)
    // [2] - mode 
    // [3] - itemid
    // [4] - curid
    // [5] - action
    // [6] - param
    protected function setitems($data, &$curval, &$validation, &$indx,&$indd)
    {
        if ($validation == $this->valindx[$indx]['validate']) {
            $this->setattr($this->valindx[$indx]['name'], $curval);
            $indx++;
            $indd++;
            if ($indd >= count($data)) {
                return;
            }
            $curval = trim($data[$indd]);
            $validation = Common_data::check_uuid($curval);
        } else {
            $indx++;
        }    
        if ($indx>6) {
            return;
        }
        $this->setitems($data, $curval, $validation, $indx,$indd);
    }        
    public function setcontext($data)
    {
        if (User::isAuthorized())
        {
            $this->setattr('USERNAME',User::getUserName($_SESSION['user_id']));
        }
        $this->context['TITLE'] = DCS_COMPANY_SHORTNAME.' '.$this->context['USERNAME'];
        $indx = 1;
        if (empty($data[$indx])) {
            return;
        }
        $curval = trim($data[$indx]);
        $indd = $indx;
        if (strtolower($curval) == 'config') {
            $this->setattr('PREFIX', 'CONFIG');
            $indx++;
            $indd++;
            if (empty($data[$indx])) {
                return;
            }
            $curval = trim($data[$indx]);
        } else {
            $indx++;
        }
        $validation = Common_data::check_uuid($curval);
        $this->setitems($data, $curval, $validation, $indx, $indd);
        $this->get_context_data();
    }   
    public function get_context_data()
    {        
        foreach($_POST as $pkey=>$val)
        {
            $key = strtolower($pkey);
            if (strpos($key,'name_')===false){
                $pval = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                $this->context['DATA'][strtolower($key)]=array('id' => $pval,'name' => $pval);
            }
        }
        foreach($_POST as $pkey=>$val)
        {
            $key = strtolower($pkey);
            if (strpos($key,'name_')===false){
                continue;
            }
            $pval = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            $curkey = substr($key,5);
            $this->context['DATA'][$curkey]['name']=$pval;
            if ($pval==DCS_EMPTY_ENTITY) {
                $this->context['DATA'][$key]['name']='';
            }
        }
        foreach($_GET as $pkey=>$val)
        {
            $key = strtolower($pkey);
            if (strpos($key,'name_')===false){
                $pval = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                $this->context['DATA'][$key]=array('id' => $pval,'name' => $pval);
            }
        }
        foreach($_GET as $pkey=>$val)
        {
            $key = strtolower($pkey);
            if (strpos($key,'name_')===false){
                continue;
            }
            $pval = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            $curkey = substr($key,5);
            $this->context['DATA'][$curkey]['name']=$pval;
            if ($pval==DCS_EMPTY_ENTITY) {
                $this->context['DATA'][$key]['name']='';
            }
        }
    }
    public function setattr($attrname, $attrval)
    {
        if (array_key_exists($attrname, $this->context)) {
            if (($attrname==='MODE')||($attrname==='ACTION')) {
                if ($attrval === '') {
                    return FALSE;
                }
                $attrval = strtoupper($attrval);
                if (($attrname==='MODE')&&($attrval==='CONFIG')) {
                    $this->context['PREFIX'] = '/config';
                    $this->context['ACTION'] = 'EDIT';
                }
//                if ($attrname==='ACTION') {
//                    if(!CollectionSet::isExistCollItemByName('Action',$attrval)) {
//                        return FALSE;
//                    }
//                }    
            }
            $this->context[$attrname] = $attrval;
        } else {
            return FALSE;
        }
        return TRUE;
    }        
    public function getattr($attrname)
    {
        if (array_key_exists($attrname, $this->context)) {
            return $this->context[$attrname];
        }
        return '';
    }        
    public function getsubsystems()
    {
        $arSubSystems = array();
        if ((User::isAdmin())&&($this->context['PREFIX']==='CONFIG')) {
            $arSubSystems = Mditem::getAllMDitems();
        } else { 
            $cur_interface = User::getUserInterface();
            if ($cur_interface) {
                $arSubSystems = DataManager::getInterfaceContents($cur_interface);
            } else {
                $arSubSystems = DataManager::getSubSystems();
            }
        }
        foreach($arSubSystems as $is) 
        {
            $this->context['MENU'][] = array('ID' => $is['id'],
                                        'NAME' => $is['name'],
                                        'SYNONYM' => trim($is['synonym'])
                                            );
        }
        return $arSubSystems;
    }        
}

