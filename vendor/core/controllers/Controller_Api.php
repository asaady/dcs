<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\App\Api\ApiAcceptOtk;
use Dcs\Vendor\Core\Models\Common_data;
use Dcs\Vendor\Core\Models\DcsContext;

class Controller_API extends Controller
{

    function action_index()
    {
        header('Content-type: application/xml');
        $context = DcsContext::getcontext();
        echo Common_data::toXml($context); 
    }
    function action_view()
    {
        $this->action_index();
    }
    function action_acceptotk()
    {
        header('Content-type: application/xml');
        $data= ApiAcceptOtk::getdata($context->getattr('DATA'));
        echo Common_data::toXml($data); 
    }
    function action_denyaccess()
    {
        Common_data::toXml(array('msg'=>'Deny access'));
    }
}

