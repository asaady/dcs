<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use Dcs\Vendor\Core\Controllers\Controller_Error;

class Route {
    protected $routes;
    protected $action_name = '';
    protected $controller_name = '';
    protected $controller_file = '';
    protected $controller_path = '';
    protected $context;
    
    function __construct()
    {
//        $prefix = array('ENTERPRISE','AUTH','API','CONFIG');
//        $modes = array('FORM','DOWNLOAD','AJAX');
        $url = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
        $pos = strpos($url,'?');
        $routes = $url;
        $data = '';
        if ($pos !== FALSE) {
            $routes = substr($url, 0,$pos);
            $data = substr($url, $pos);
        }
        $routes = explode('/', $routes);

        $this->context = new DcsContext;
        $this->context->setcontext($routes);
        if (!User::isAuthorized()) {
            if ($this->context->getattr('PREFIX') !== 'AUTH') { 
                $this->context->setattr('PREFIX','AUTH');
                $this->context->setattr('ACTION','VIEW');
            }
        } elseif (($this->context->getattr('PREFIX') !== 'ERROR')&&
                  ($this->context->getattr('MODE') === 'FORM')) {
            $arSubSystems = $this->context->getsubsystems();
            if (($this->context->getattr('ITEMID') == '')&&
                (count($arSubSystems))) {
                $ritem = reset($arSubSystems);
                $this->context->setattr('ITEMID',$ritem['id']);
            }
        }
        if ($this->context->getattr('ITEMID')) {
            $item = self::getContentByID($this->context->getattr('ITEMID'),
                                         $this->context->getattr('PREFIX'));
            $classname =  $item['classname'];
            if (!$item) {
                $newobj = DataManager::getNewObjectById($this->context->getattr('ITEMID'));
                if (!$newobj) {
                    $this->seterrorcontext();
                } else {
                    $classname = $newobj['classname'];
                }
            }
            $this->context->setattr('CLASSNAME',$classname);
        }
        $this->controller_path = "/vendor/core/controllers";
    }
    public function error_route($ex='',$message = '')
    {
        $ex_message = 'error';
        if ($message) {
            $ex_message = $message;
        }
        $ex_code = '';
        $data = array('msg' => $ex_message);
        if ($ex) {
            $ex_code = $ex->getCode();
            $data = array(
                'msg' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            );
        }
        $action = $this->get_action_error($ex_code);
        $this->seterrorcontext();
        $context = $this->context->getcontext();
        $controller = new Controller_Error($context);
        $controller->$action($context,$data);
    }        

    public function start()
    {
        $this->controller_name = $this->setcontrollername();
        $controller_namespace = "\\Dcs".str_replace("/","\\",ucwords($this->controller_path,"/"))."\\";
        $controllername = $controller_namespace.$this->controller_name;
        $this->action_name = 'action_'.strtolower($this->context->getattr('ACTION'));
        if ($this->context->getattr('MODE') === 'AJAX') {
            $this->action_name = 'action_'.strtolower($this->context->getattr('COMMAND'));
        }
        $context = $this->context->getcontext();
        if (!class_exists($controllername)) {
            $this->error_route('','controller '.$controllername.': not exist');
            return;
        } else {
            try {
                $controller = new $controllername($context);
            } catch (DcsException $ex) {
                $this->error_route($ex);
                return;
            }
        }   
        $action = $this->action_name;
        if(!method_exists($controller, $action)) {
            $this->error_route('','controller '.$controllername.': action '.$action.' not exist');
            return;
        }
        try {
            $controller->$action($context);
        } catch (DcsException $ex) {
            $this->error_route($ex);
            return;
        }    
    }    
    function get_action_error($ex_code = '')
    {
        if ($this->context->getattr('MODE') === 'AJAX') {
            return 'action_json';
        }
        if ($ex_code === DCS_DENY_ACCESS) {
            return 'action_denyaccess';
        }
        return 'action_error';
    }
    function seterrorcontext()
    {
        $this->context->setattr('PREFIX','ERROR');
        $this->context->setattr('COMMAND','');
    }
    public static function getContentByID($itemid, $prefix='') 
    {
        $sql = "SELECT 'EntitySet' as classname, md.name, md.id, md.synonym, ct.name as typename FROM \"MDTable\" as md inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and NOT ct.name in ('Cols','Comps','Regs')
                UNION SELECT 'CollectionSet', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and ct.name in ('Cols','Comps')
                UNION SELECT 'Register', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and ct.name = 'Regs'
                UNION SELECT 'Mdentity', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and NOT ct.name in ('Cols','Comps','Regs')
                UNION SELECT 'Mdcollection', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and ct.name in ('Cols','Comps')
                UNION SELECT 'Mdregister', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and ct.name = 'Regs'
                UNION SELECT 'MdentitySet', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:itemid
                UNION SELECT 'Entity', et.name, et.id, et.name, md.name FROM \"ETable\" as et 
                    INNER JOIN \"MDTable\" as md  
                        inner join \"CTable\" as ct 
                            inner join \"MDTable\" as mditem 
                            on ct.mdid=mditem.id 
                        on md.mditem=ct.id 
                        and mditem.name='MDitems' 
                    ON et.mdid = md.id 
                    WHERE et.id=:itemid  and NOT ct.name = 'Items'
                UNION SELECT 'Item', et.name, et.id, et.name, md.name FROM \"ETable\" as et 
                    INNER JOIN \"MDTable\" as md  
                        inner join \"CTable\" as ct 
                            inner join \"MDTable\" as mditem 
                            on ct.mdid=mditem.id 
                        on md.mditem=ct.id 
                        and mditem.name='MDitems' 
                    ON et.mdid = md.id 
                    WHERE et.id=:itemid  and ct.name = 'Items'
                UNION SELECT 'Sets', et.name, et.id, et.name, md.name FROM \"ETable\" as et 
                    INNER JOIN \"MDTable\" as md  
                        inner join \"CTable\" as ct 
                            inner join \"MDTable\" as mditem 
                            on ct.mdid=mditem.id 
                        on md.mditem=ct.id 
                        and mditem.name='MDitems' 
                    ON et.mdid = md.id 
                    WHERE et.id=:itemid  and ct.name = 'Sets'
                UNION SELECT 'CollectionItem', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id AND md.name<>'MDitems' WHERE ct.id=:itemid
                UNION SELECT 'EProperty', mp.name, mp.id, mp.synonym, md.name  FROM \"MDProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid
                UNION SELECT 'CProperty', cp.name, cp.id, cp.synonym, md.name FROM \"CProperties\" as cp INNER JOIN \"MDTable\" as md ON cp.mdid=md.id WHERE cp.id=:itemid 
                UNION SELECT 'RProperty', mp.name, mp.id, mp.synonym, md.name  FROM \"RegProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid";

        $artt = array();
        $sth = DataManager::dm_query($sql,array('itemid' => $itemid, 'prefix' => $prefix));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
    public static function getActionList($id, $mode, $edit_mode = '') {
        $toset = false;
        $item = self::getContentByID($id);
        $classname = $item['classname'];
        if ($item['typename'] == 'Comps') {
            $classname = 'Component';
        }
        return DataManager::getActionsbyItem($classname, $mode, $edit_mode);
    }
    public function setcontrollername()
    {
        if ($this->context->getattr('PREFIX') == 'ERROR') {
            return 'Controller_Error';
        }
        if ($this->context->getattr('MODE') == 'AJAX') {
            return 'Controller_Ajax';
        }
        if ($this->context->getattr('MODE') == 'DOWNLOAD') {
            return 'Controller_Download';
        }
        if ($this->context->getattr('PREFIX') == 'API') {
            return 'Controller_Api';
        }
        if ($this->context->getattr('PREFIX') == 'AUTH') {
            return 'Controller_Auth';
        }
        if (($this->context->getattr('PREFIX') == 'CONFIG')||
            ($this->context->getattr('PREFIX') == 'ENTERPRISE')) {
            return 'Controller_Sheet';
        }
        return 'Controller_Error';
    }        
}
