<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\UploadObject;

class Controller_API extends Controller
{

	function __construct()
	{
	}
	
	function action_index($arResult)
	{
            header('Content-type: application/xml');
            echo tzVendor\Common_data::toXml($arResult); 
        }
	function action_acceptotk($arResult)
	{
            header('Content-type: application/xml');
            echo tzVendor\Common_data::toXml($arResult); 
        }
}

