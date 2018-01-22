<?php
namespace Dcs\Vendor\Core\Models;

//use Dcs\Vendor\Controllers as Controllers;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;

class Route {
    protected $routes;
    protected $action_name = '';
    protected $controller_name = '';
    protected $controller_file = '';
    protected $controller_path = '';
    protected $modes;
    protected $context;
    
    function __construct()
    {
        $this->modes = array(
        'ENTERPRISE' => function() {
                $item = self::getContentByID($this->context->getattr('ITEMID'),$this->context->getattr('PREFIX'));
                $this->controller_name = 'Controller_Head';    
                $this->context->setattr('CLASSNAME', $item['classname']);
            },
        "AUTH" => function() {
                $this->controller_name = 'Controller_Auth';
                },
        "DOWNLOAD" => function() {
                $this->controller_name='Controller_Download';
                },
        "API" => function() {
                $this->controller_name = 'Controller_Api';
                },
        "PRINT" => function() {
                $item = self::getContentByID($this->context->getattr('ITEMID'),$this->context->getattr('PREFIX'));
                $this->context->setattr('CLASSNAME', $item['classname']);
                $this->context->setattr('CLASSTYPE', $item['classtype']);
                $ent = new Entity($this->context->getattr('ITEMID'));
                $compname = $ent->getmdentity()->getname();
                $model_name = 'Prn'.$compname;
                $model_file = $model_name.'.php';
                $model_path = "app/components/prnforms/".strtolower($compname)."/".$model_file;
                if(file_exists($model_path))
                {
                    $tcontroller_name = 'Controller_'.$model_name;
                    $tcontroller_path = "app/components/prnforms/".strtolower($compname)."/".$tcontroller_name.'.php';
                    if(file_exists($tcontroller_path))
                    {
                        $this->controller_name = $tcontroller_name;
                        $this->controller_path = "app/components/prnforms/".strtolower($compname);
                    }
                }
                },
        "AJAX" => function() {
                $item = self::getContentByID($this->context->getattr('ITEMID'),$this->context->getattr('PREFIX'));
                $this->controller_name='Controller_Ajax';
                $this->action_name = "action_".strtolower($this->context->getattr('ACTION'));
                $this->context->setattr('CLASSNAME', $item['classname']);
                $this->context->setattr('CLASSTYPE', $item['classtype']);
            }
        );
        $routes = str_replace("&","/",filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING));
        $routes = str_replace("?","/",$routes);
        $routes = explode('/', $routes);
        $this->context = new DcsContext;
        $this->context->setcontext($routes);
        $this->controller_name = 'Controller_404';
        $this->controller_path = "/vendor/core/controllers";
        if (!User::isAuthorized()) {
            if ($this->context->getattr('MODE') !== 'AUTH') { 
                $this->context->setattr('MODE','AUTH');
                $this->context->setattr('ACTION','VIEW');
            }
        } else {
            if (array_key_exists($this->context->getattr('MODE'), $this->modes) !== FALSE) {
                $arSubSystems = $this->context->getsubsystems();
                if (($this->context->getattr('ITEMID') == '')&&(count($arSubSystems))) {
                    $ritem = reset($arSubSystems);
                    $this->context->setattr('ITEMID',$ritem['id']);
                }
            }    
        }
        $handlername = $this->modes[$this->context->getattr('MODE')];
        $handlername();
    }
    public function start()
    {
        $this->action_name = 'action_'. strtolower($this->context->getattr('ACTION'));
        $controller_namespace = "\\Dcs".str_replace("/","\\",ucwords($this->controller_path,"/"))."\\";
        $controllername = $controller_namespace.$this->controller_name;
        if (class_exists($controllername)) {
            try {
                $controller = new $controllername($this->context->getcontext());
            } catch (Exception $ex) {
                $this->ErrorPage404();
            }
        } else {
            $this->ErrorPage404();        
        }
        if ($this->context->getattr('CURID') !== '') {
            $prop = $controller->model->get_property($this->context->getattr('CURID'));
            if ($prop['valmdtypename'] == 'Sets') {
                // это запрос на табличную часть сущности
                $setaction = 'view';
                if ($this->context->getattr('ACTION') == 'EDIT') {
                    $setaction = 'edit';
                }    
                $this->action_name = 'action_set_'.$setaction;
                $this->context->setattr('ACTION', 'SET_'.strtoupper($setaction));
            }
        }
        $action = $this->action_name;
        if(method_exists($controller, $action)) {
            $controller->$action($this->context->getcontext());
        } else {
//            die($controllername." ".$this->action_name." classname=".$this->context->getattr('CLASSNAME'));
            $this->ErrorPage404();
        }
    }    
    function ErrorPage404($context)
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
        header("Error: 404 Not Found");
        header("Status: 404 Not Found");
        header('Location:'.$host);
        exit();
    }
    public static function getContentByID($itemid, $prefix='') 
    {
        $sql = "SELECT 0 as rank, 'EntitySet' as classname, 'Head' as classtype, md.name, md.id, md.synonym, ct.name as typename FROM \"MDTable\" as md inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and NOT ct.name in ('Cols','Comps','Regs')
                UNION SELECT 0,'CollectionSet', 'Head', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and ct.name in ('Cols','Comps')
                UNION SELECT 0,'Register', 'Head', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix <> 'CONFIG' and ct.name = 'Regs'
                UNION SELECT 0,'Mdentity', 'Head', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and NOT ct.name in ('Cols','Comps','Regs')
                UNION SELECT 0,'Mdcollection', 'Head', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and ct.name in ('Cols','Comps')
                UNION SELECT 0,'Mdregister', 'Head', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid and :prefix = 'CONFIG' and ct.name = 'Regs'
                UNION SELECT 2,'MdentitySet', 'Head', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:itemid
                UNION SELECT 1,'Entity', 'Head', et.name, et.id, et.name, md.name FROM \"ETable\" as et INNER JOIN \"MDTable\" as md ON et.mdid = md.id WHERE et.id=:itemid
                UNION SELECT 5,'CollectionItem', 'Head', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id AND md.name<>'MDitems' WHERE ct.id=:itemid
                UNION SELECT 3,'EProperty', 'Head', mp.name, mp.id, mp.synonym, md.name  FROM \"MDProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid
                UNION SELECT 6,'CProperty', 'Head', cp.name, cp.id, cp.synonym, md.name FROM \"CProperties\" as cp INNER JOIN \"MDTable\" as md ON cp.mdid=md.id WHERE cp.id=:itemid 
                UNION SELECT 8,'RProperty', 'Head', mp.name, mp.id, mp.synonym, md.name  FROM \"RegProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid";

        $artt = array();
//        $artt[] = DataManager::createtemptable($sql, 'tt0', array('itemid' => $itemid, 'prefix' => $prefix));
//        $sql = "SELECT min(rank) as rank, id FROM tt0 GROUP BY id";
//        $artt[] = DataManager::createtemptable($sql, 'tt1');
//        $sql = "SELECT tt0.classname, tt0.classtype, tt0.name, tt1.id, tt0.synonym, tt0.typename FROM tt0 inner join tt1 on tt0.rank=tt1.rank and tt0.id=tt1.id";
        $sth = DataManager::dm_query($sql,array('itemid' => $itemid, 'prefix' => $prefix));
//        DataManager::droptemptable($artt);
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
}
