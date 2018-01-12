<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\Register;

class Controller_Register extends Controller
{
	function __construct($id)
	{
		$this->model = new Register($id);
		$this->view = new View();
	}
	
	function action_index($context)
	{
		$data = $this->model->get_data($context['MODE']);
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/entityset_view.php";
                $this->view->generate($context, $data);
	}
	function action_view($context)
	{
                $this->action_index($context);
	}
}