<?php
namespace Dcs\Vendor\Core;

//use Dcs\Vendor\Controllers as Controllers;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;
//use Dcs\Vendor\Controllers\Controller_MdentitySet;
//use dcs\vendor\core\InputDataManager;
//use dcs\vendor\core\Common_data;
//use dcs\vendor\core\User;
//use dcs\vendor\core\DcsContext;

class Route {
    protected $routes;
    protected $classname = '';
    protected $action_name = '';
    protected $controller_name = '';
    protected $controller_file = '';
    protected $controller_path = '';
    protected $controller_namespace = '';
    protected $modes;
    protected $context;
    
    function __construct()
    {
        $this->modes = array(
        'ENTERPRISE' => function() {
                $item = self::getContentByID($this->context->getattr('ITEMID'));
                $this->classname = $item['classname'];
                $this->controller_name = 'Controller_'.$item['classname'];    
                if ($this->classname == 'CollectionItem')
                {
                    if ($this->context->getattr('ACTION') != 'EDIT')
                    {
                        $coll = new CollectionItem($this->context->getattr('ITEMID'));
                        if ($coll->getcollectionset()->getmditem()->getname()=='Comps')
                        {
                            $model_name = $coll->getname();
                            $compname = $model_name;
                            $model_file = $compname.'.php';
                            $model_path = "app/components/".strtolower($coll->getcollectionset()->getname())."/".strtolower($compname)."/".$model_file;
                            if(file_exists($model_path))
                            {
                                $tcontroller_name = 'Controller_'.$model_name;
                                $tcontroller_path = "app/components/".strtolower($coll->getcollectionset()->getname())."/".strtolower($compname);
                                if(file_exists($this->controller_path."/".$tcontroller_name.'.php'))
                                {
                                    $this->controller_name = $tcontroller_name;
                                    $this->controller_path = $tcontroller_path;
                                }
                            }
                        }    
                    } 
                }
            },
        'CONFIG' => function() {
                $item = self::getContentByID();
                if ($item['classname']=='EntitySet')
                {
                    $item['classname']='Mdentity';
                }    
                $this->classname = $item['classname'];
                $this->controller_name = 'Controller_'.$item['classname'];    
            },
        "AUTH" => function() {
                $this->controller_name = 'Controller_Auth';
                    },
        "DOWNLOAD" => function() {
                $this->controller_name='Controller_Download';
                    },
        "API" => function() {
                $this->controller_name = 'Controller_API';
                },
        "PRINT" => function() {
                $item = self::getContentByID();
                $this->classname = $item['classname'];
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
               $this->controller_name='Controller_Ajax';
            },
        );
        $this->classname = '';
        $this->action_name = 'action_index';
        $this->controller_name = 'Controller_404';
        $this->controller_path = "/vendor/controllers";
        $this->controller_namespace = "Dcs\\Vendor\\Controllers\\";
    }
    public function start()
    {
        $routes = explode('/', filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING));
        $this->context = new DcsContext;
        $this->context->setcontext($routes);
        $this->action_name = 'action_'. strtolower($this->context->getattr('ACTION'));
        $arSubSystems = $this->context->getsubsystems();
        if (($this->context->getattr('ITEMID') == '')&&(count($arSubSystems))) {
            $ritem = '';
            foreach ($arSubSystems as $row)
            {
                $ritem = $row['id'];
                break;
            }   
            $this->context->setattr('ITEMID',$ritem);
        }
        $handlername = $this->modes[$this->context->getattr('MODE')];
        $handlername();
        $controllername = $this->controller_namespace.$this->controller_name;
        if ($this->classname=='')
        {
            $controller = new $controllername;
        } 
        else 
        {
            $controller = new $controllername($this->context->getattr('ITEMID'));
            //die(var_dump($controllername));
            //$controller = new Controller_MdentitySet($this->context->getattr('ITEMID'));
        }
        $action = $this->action_name;
        if(method_exists($controller, $action))
        {
            $controller->$action($this->context->getcontext());
        }
    }    
    function ErrorPage404()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
		header("Error: 404 Not Found");
		header("Status: 404 Not Found");
		header('Location:'.$host.'404');
    }
    public static function getContentByID($itemid) 
    {
        $sql = "SELECT 0 as rank,'EntitySet' as classname, md.name, md.id, md.synonym, ct.name as typename FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='MDitems' WHERE md.id=:itemid
                UNION SELECT 1,'Entity', et.name, et.id, et.name, md.name FROM \"ETable\" as et INNER JOIN \"MDTable\" as md ON et.mdid = md.id WHERE et.id=:itemid
                UNION SELECT 2,'MdentitySet', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:itemid
                UNION SELECT 3,'Mdproperty', mp.name, mp.id, mp.synonym, md.name  FROM \"MDProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid
                UNION SELECT 5,'CollectionItem', ct.name, ct.id, ct.synonym, md.name FROM \"CTable\" as ct INNER JOIN \"MDTable\" as md ON ct.mdid=md.id WHERE ct.id=:itemid
                UNION SELECT 6,'Cproperty', cp.name, cp.id, cp.synonym, md.name FROM \"CProperties\" as cp INNER JOIN \"MDTable\" as md ON cp.mdid=md.id WHERE cp.id=:itemid 
                UNION SELECT 7,'Register', md.name, md.id, md.synonym, ct.name FROM \"MDTable\" as md  inner join \"CTable\" as ct inner join \"MDTable\" as mditem on ct.mdid=mditem.id on md.mditem=ct.id and mditem.name='Regs' WHERE md.id=:itemid
                UNION SELECT 8,'RegProperty', mp.name, mp.id, mp.synonym, md.name  FROM \"RegProperties\" as mp INNER JOIN \"MDTable\" as md ON mp.mdid=md.id WHERE mp.id=:itemid";

        $artt = array();
        $artt[] = DataManager::createtemptable($sql, 'tt0', array('itemid' => $itemid));
        $sql = "SELECT min(rank) as rank, id FROM tt0 GROUP BY id";
        $artt[] = DataManager::createtemptable($sql, 'tt1');
        $sql = "SELECT tt0.classname, tt0.name, tt1.id, tt0.synonym, tt0.typename FROM tt0 inner join tt1 on tt0.rank=tt1.rank and tt0.id=tt1.id";
        $sth = DataManager::dm_query($sql);
        DataManager::droptemptable($artt);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
