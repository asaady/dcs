<?php
use tzVendor\Controller;
use tzVendor\Download;


class Controller_Download extends Controller
{

	function __construct($id)
	{
		$this->model = new Download($id);
	}
	
	function action_index($arResult)
	{
		return $this->model->get_data($arResult['CURID']);
	}
	function action_error($arResult)
	{
            header('HTTP/1.1 404 Not Found');
            header("Error: 404 Not Found");
            header("Status: 404 Not Found");
            exit;
	}
}
