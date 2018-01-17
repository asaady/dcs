<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\ItemActions;

class Controller_ItemActions extends Controller
{

	function __construct($id)
	{
		$this->model = new ItemActions($id);
		$this->view = new View();
	}
	
	function action_index($context)
	{
		$data = $this->model->get_data();
                $context['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."public/js/entity_view.js";
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/itemactions_view.php";
		$this->view->generate($context, $data);
	}
}
