<?php
namespace tzVendor;

class InputDataManager {
    protected $itemid;
    protected $curid;
    protected $action;
    protected $command;
    protected $prefix;
    protected $mode;
    protected $version;
    protected $data;
    
    public function __construct() 
    {
        $this->data=array();
        foreach($_POST as $key=>$val)
        {
            if (strpos($key,'name_')===false){
                $pval = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                switch ($key){
                    case 'version': $this->version = $pval; break;
                    case 'mode': $this->mode = $pval; break;
                    case 'action': $this->action = $pval; break;
                    case 'command': $this->command = $pval; break;
                    case 'curid': $this->curid = $pval; break;
                    case 'itemid': $this->itemid = $pval;
                    default:  
                        $this->data[$key]=array();
                        $this->data[$key]['id']  = $pval;
                        $this->data[$key]['name']= $pval; 
                }
            }
        }
        foreach($_POST as $key=>$val)
        {
            if (strpos($key,'name_')===false){
                continue;
            }
            $pval = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            $curkey = substr($key,5);
            $this->data[$curkey]['name']=$pval;
            if ($pval==TZ_EMPTY_ENTITY) {
                $this->data[$key]['name']='';
            }
        }
        foreach($_GET as $key=>$val)
        {
            if (strpos($key,'name_')===false){
                $pval = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                switch ($key){
                    case 'version': $this->version = $pval; break;
                    case 'mode': $this->mode = $pval; break;
                    case 'action': $this->action = $pval; break;
                    case 'prefix': $this->prefix = $pval; break;
                    case 'command': $this->command = $pval; break;
                    case 'curid': $this->curid = $pval; break;
                    case 'itemid': $this->itemid = $pval;
                    default:  
                        $this->data[$key]=array();
                        $this->data[$key]['id']  = $pval;
                        $this->data[$key]['name']= $pval; 
                }
            }
        }
    }
    
    public function getdata() {
        return $this->data;
    }
    public function getitemid() {
        return $this->itemid;
    }
    public function getcurid() {
        return $this->curid;
    }
    public function getmode() {
        return $this->mode;
    }
    public function getaction() {
        return $this->action;
    }
    public function getcommand() {
        return $this->command;
    }
    public function getprefix() {
        return $this->prefix;
    }
    public function getversion() {
        return $this->version;
    }
            
}

