<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\App\Api\ApiAcceptOtk;
use Dcs\Vendor\Core\Models\Common_data;

class Controller_API extends Controller
{

	function __construct()
	{
	}
	
	function action_index($context)
	{
            header('Content-type: application/xml');
            echo Common_data::toXml($context); 
        }
	function action_acceptotk($context)
	{
            header('Content-type: application/xml');
            $data= ApiAcceptOtk::getdata($context['PARAM']);
            echo Common_data::toXml($data); 
        }
}

