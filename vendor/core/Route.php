<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

use tzVendor\Common_data;

class Route {
        private static function controller_run($controller_path,$controller_name,$action_name,$classname, $arResult)
        {    
            if ($arResult['MODE']=='DOWNLOAD')    
            {
                $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/controller_download.php";
                $controller_name='Controller_Download';
                if($controller_name=='Controller_404')
                {
                    $action_name = 'action_error';
                }
                else 
                {
                    $action_name = 'action_index';
                }
            }    
            if(file_exists($controller_path))
            {
                require_once("$controller_path");
            } 
            else 
            {
                die("file not found: $controller_path");
            }
            if ($classname=='')
            {
                $controller = new $controller_name();
            } 
            else 
            {
                $controller = new $controller_name($arResult['ITEMID']);
            }
            $action = $action_name;
            if(method_exists($controller, $action))
            {
                $controller->$action($arResult);
            }
            else
            {
                if ($arResult['MODE']=='API') 
                {
                    $data = array();
                    $data['STATUS']="ERROR";
                    $data['MESSAGE']="action not found : $action for controller $controller_name";
                    Common_data::_log(TZ_API_LOG,$data['MESSAGE']);
                    header('Content-type: application/xml');
                    echo Common_data::toXml($data);
                    return;
                }   
                else
                {
                    die("action not found : $action for controller $controller_name");
                }    
            }
        
        }        
        private static function getController($routes,$arResult, $step, &$controller_path,&$controller_name,&$action_name,&$classname,&$ritem)
        {
            $curid='';
            $param='';
            if ( !empty($routes[2+$step]) )
            {
                $act = strtolower(trim($routes[2+$step]));
                $res = CollectionSet::isExistCollItemByName('Action',$act);
                if ($res)
                {
                    if ( !empty($routes[3+$step]) )
                    {
                        $validation = Common_data::check_uuid($routes[3+$step]);
                        if ($validation) 
                        {    
                            $curid=trim($routes[3+$step]);
                        }
                    }
                    $arResult['ACTION']= strtoupper($act);
                    $action_name = 'action_'.$act;
                }
                else
                {
                    $validation = Common_data::check_uuid($routes[2+$step]);
                    if ($validation) 
                    {    
                        $curid=trim($routes[2+$step]);
                        if ( !empty($routes[3+$step]) )
                        {    
                            $param=trim($routes[3+$step]);
                            $act = strtolower($param);
                            $res = CollectionSet::isExistCollItemByName('Action',$act);
                            if ($res)
                            {
                                $arResult['ACTION']= strtoupper($act);
                                $action_name = 'action_'.$act;
                            }
                        }    
                    }    
                }    
            }	
            $validation = Common_data::check_uuid($ritem);
            if (!$validation) 
            {
                $classname = '';
                $action_name = 'action_index';
                $controller_name = 'Controller_404';    
                $controller_file = strtolower($controller_name).'.php';
                $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
                $item = array();
            }
            else 
            {   
                $item = DataManager::getContentByID($ritem);
                if ($arResult['MODE']=='CONFIG')
                {
                    if ($item['classname']=='EntitySet')
                    {
                        $item['classname']='Mdentity';
                    }    
                }
                $arResult['CURID']=$curid;
                $arResult['ITEMID']=$ritem;
                $arResult['PARAM']=$param;
                if (!$item)
                {
                    $classname = '';
                    $action_name = 'action_index';
                    $controller_name = 'Controller_404';    
                    $controller_file = strtolower($controller_name).'.php';
                    $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
                } 
                else 
                {
                    $classname = $item['classname'];
                    $controller_name = 'Controller_'.$item['classname'];    
                    $controller_file = strtolower($controller_name).'.php';
                    $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
                    if ($item['classname']=='CollectionItem')
                    {
                        if ($arResult['ACTION']<>'EDIT')
                        {
                            $coll = new CollectionItem($ritem);
                            if ($coll->getcollectionset()->getmditem()->getname()=='Comps')
                            {
                                $model_name = $coll->getname();
                                $compname = $model_name;
                                $model_file = $compname.'.php';
                                $model_path = "app/components/".strtolower($coll->getcollectionset()->getname())."/".strtolower($compname)."/".$model_file;
                                if(file_exists($model_path))
                                {
                                    if (strtolower($item['typename'])=='reps')
                                    {    
                                        $tcontroller_name = 'Controller_'.$model_name;
                                        $tcontroller_path = "app/components/".strtolower($coll->getcollectionset()->getname())."/".strtolower($compname)."/".$tcontroller_name.'.php';
                                        if(file_exists($controller_path))
                                        {
                                            $classname = $item['classname'];
                                            $controller_path = $tcontroller_path;
                                            $controller_name = $tcontroller_name;
                                            include $model_path;
                                            include $controller_path;
                                        }
                                    }
                                    elseif (strtolower($item['typename'])=='utils')
                                    {    
                                        $tcontroller_name = 'Controller_'.$model_name;
                                        $tcontroller_path = "app/components/".strtolower($coll->getcollectionset()->getname())."/".strtolower($compname)."/".$tcontroller_name.'.php';
                                        if(file_exists($controller_path))
                                        {
                                            $classname = $item['classname'];
                                            $controller_path = $tcontroller_path;
                                            $controller_name = $tcontroller_name;
                                            include $model_path;
                                            include $controller_path;
                                        }
                                    }    

                                }
                                else 
                                {
                                    $classname = '';
                                    $action_name = 'action_index';
                                    $controller_name = 'Controller_404';    
                                    $controller_file = strtolower($controller_name).'.php';
                                    $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
                                    error_log ("file not exist: ".$model_path, 0);
                                }
                            }    
                        } 
                    }
                    else 
                    {
                        if ($curid!='')
                        {    
                            if ($item['classname']=='Entity')
                            {
                                $curitem = DataManager::getContentByID($curid);
                                if ($curitem['classname']=='Mdproperty')
                                {    
                                    $arprop = Mdproperty::getProperty($curid);
                                    if ($arprop['valmdtypename']=='Sets')
                                    {
                                        $arResult['ACTION'] = 'SET_'.$arResult['ACTION'];
                                    }    
                                }    
                            }    
                        }    
                    }
                }
            }
            return $arResult;    
        }        


        static function start()
	{
                
            $controller_name = 'controller';
            $action_name = 'action_index';

            // контроллер и действие по умолчанию


            $routes = explode('/', filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING));
            $ritem = '';
            if ( !empty($routes[1]) )
            {
                $ritem = trim($routes[1]);
            }	
            $arResult = array();
            $step=0;
            $arResult['PREFIX']='';
            $arResult['ACTION']='VIEW';
            $arResult['PAGE']=1;
            $arResult['CURID']='';
            $arResult['TITLE']=TZ_COMPANY_SHORTNAME;
            $arResult['ITEMID']='';
            $arResult['MODE']='ENTERPRISE';
            $arResult['PARAM']='';
            if (strtolower(trim($ritem))=='api')
            {
                //Это вызов API 
                $controller_name = 'Controller_API';
                $controller_file = strtolower($controller_name).'.php';
                $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
                $arResult['ACTION']= strtoupper(trim($routes[2]));
                $arResult['MODE']='API';
                $classname='';
                if ( !empty($routes[2]) )
                {
                    $action_name = 'action_'. strtolower(trim($routes[2]));
                    if ( !empty($routes[3]) )
                    {
                        $arResult['PARAM'] = trim($routes[3]);
                    }	
                }	
                Route::controller_run($controller_path,$controller_name,$action_name,$classname, $arResult);
                return;
            }    
            $arSubSystems = DataManager::getSubSystems();
            if (!User::isAuthorized())
            {
                $arResult['MODE']='AUTH';
                $username = 'Anonymous';
                $item = '';
                $classname = '';
                $action_name = 'action_index';    
                $controller_name = 'Controller_Auth';
                $controller_file = strtolower($controller_name).'.php';
                $controller_path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/controllers/".$controller_file;
            }
            else 
            {
                $username = User::getUserName($_SESSION['user_id']);
                if (strtolower(trim($ritem))=='download')
                {
                    $arResult['MODE']='DOWNLOAD';
                    $ritem = trim($routes[2]);
                    $step=1;
                }    
                elseif (strtolower(trim($ritem))=='print')
                {
                    //Это вызов печатной формы
                    $arResult['MODE']='PRINT';
                    $ritem = trim($routes[2]);
                    $step=1;
                }
                else 
                {
                    if (User::isAdmin())
                    {    
                        if (strtolower(trim($ritem))=='config')
                        {
                            $arResult['MODE']='CONFIG';
                            $arResult['PREFIX']='/config';
                            $arResult['ACTION']='EDIT';
                            $step=1;
                            $ritem='';
                            if ( !empty($routes[1+$step]) )
                            {
                                $ritem = trim($routes[1+$step]);
                            }	
                            $arSubSystems = Mditem::getAllMDitems();
                        }    
                    }
                    else 
                    { 
                        $cur_interface = User::getUserInterface();

                        if ($cur_interface)    
                        {
                            $arSubSystems = DataManager::getInterfaceContents($cur_interface);
                        }    
                    }
                }
                if (!count($arSubSystems)) 
                {
                    die("main menu is empty");
                }
                if ($ritem=='')
                {
                   foreach ($arSubSystems as $row)
                   {
                       $ritem = $row['id'];
                       break;
                   }    
                }  
                $arResult = Route::getController($routes,$arResult, $step, $controller_path, $controller_name,$action_name,$classname,$ritem);
            }
            $arResult['MENU']=array();
            foreach($arSubSystems as $is) 
            {
                $arResult['MENU'][] = array('ID' => $is['id'],
                                            'NAME' => $is['name'],
                                            'SYNONYM' => trim($is['synonym'])
                                                );
            }
            $arResult['TITLE']=TZ_COMPANY_SHORTNAME.' '.$username;
            Route::controller_run($controller_path,$controller_name,$action_name,$classname, $arResult);
        }

	function ErrorPage404()
	{
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
		header("Error: 404 Not Found");
		header("Status: 404 Not Found");
		header('Location:'.$host.'404');
    }
    
}
