<?php
use dcs\vendor\core\Controller;
use dcs\vendor\core\View;
use dcs\app\api\ApiAcceptOtk;
use dcs\vendor\core\Common_data;

class Controller_API extends Controller
{

	function __construct()
	{
	}
	
	function action_index($arResult)
	{
            header('Content-type: application/xml');
            echo Common_data::toXml($arResult); 
        }
	function action_acceptotk($arResult)
	{
            header('Content-type: application/xml');
            $data= ApiAcceptOtk::getdata($arResult['PARAM']);
            echo Common_data::toXml($data); 
        }
}

