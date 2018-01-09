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
        $this->context['ACTION'] = 'VIEW';
        $this->context['PREFIX'] = '';
        $this->context['ITEMID'] = '';
        $this->context['CURID'] = '';
        $this->context['PARAM'] = '';
        $this->context['PAGE'] = 1;
        $this->context['MENU'] = array();
        $this->context['USERNAME'] = 'Anonymous';
    } 
    
    public function getcontext()
    {
        return $this->context;
    }   
    //
    // $data = array 
    // [0] - document_root
    // [1] - mode 
    // [2] - itemid
    // [3] - curid
    // [4] - action
    // [5] - param
    protected function setitems($data, &$curval, &$validation, &$indx)
    {
        if ($validation == $this->valindx[$indx]['validate']) {
            $this->setattr($this->valindx[$indx]['name'], $curval);
            $indx++;
            if ($indx>5) {
                return;
            }
            if (empty($data[$indx])) {
                return;
            }
            $curval = trim($data[$indx]);
            $validation = Common_data::check_uuid($curval);
            $this->setitems($data, $curval, $validation, $indx);
        }
    }        
    public function setcontext($data)
    {
        if (!User::isAuthorized())
        {
            $this->setattr('MODE','AUTH');
            $this->setattr('ACTION','LOGIN');
            return;
        } else {
            $this->setattr('USERNAME',User::getUserName($_SESSION['user_id']));
        }
        $this->context['TITLE'] = DCS_COMPANY_SHORTNAME.' '.$this->context['USERNAME'];
        $indx = 1;
        if (empty($data[$indx])) {
            return;
        }
        $curval = trim($data[$indx]);
        $validation = Common_data::check_uuid($curval);
        $this->setitems($data, $curval, $validation, $indx);
    }   
    
    public function setattr($attrname, $attrval)
    {
        if (array_key_exists($attrname, $this->context)) {
            if (($attrname==='MODE')||($attrname==='ACTION')) {
                $attrval = strtoupper($attrval);
                if (($attrname==='MODE')&&($attrval==='CONFIG')) {
                    $this->context['PREFIX'] = '/config';
                    $this->context['ACTION'] = 'EDIT';
                }
                if ($attrname==='ACTION') {
                    if(!CollectionSet::isExistCollItemByName('Action',$attrval)) {
                        return FALSE;
                    }
                }    
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
        if ((User::isAdmin())&&($this->context['MODE']==='CONFIG')) {
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

