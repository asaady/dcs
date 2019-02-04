<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dcs\App\Components\Utils\Uploadobject;

use Dcs\Vendor\Core\Models\Route;
use Dcs\Vendor\Core\Models\DcsContext;

class Uploadobject_route extends Route
{
    public function __construct() {
        parent::__construct();
        $this->controller_path = "/app/components/utils/uploadobject";
    }
    public function setcontrollername()
    {
        $context = DcsContext::getcontext();
        if ($context->getattr('PREFIX') == 'ERROR') {
            return 'Controller_Error';
        }
        return 'Controller_Uploadobject';
    }    
    public function start()
    {
        $context = DcsContext::getcontext();
        $this->controller_name = $this->setcontrollername();
        $controller_namespace = "\\Dcs".str_replace("/","\\",ucwords($this->controller_path,"/"))."\\";
        $controllername = $controller_namespace.$this->controller_name;
        $this->action_name = 'action_'.strtolower($context->getattr('COMMAND'));
        $controller = 0;
        if (!class_exists($controllername)) {
            $this->error_route('','controller '.$controllername.': not exist');
            return;
        } else {
            try {
                $controller = new $controllername();
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
            $controller->$action();
        } catch (DcsException $ex) {
            $this->error_route($ex);
            return;
        }    
    }    
}
