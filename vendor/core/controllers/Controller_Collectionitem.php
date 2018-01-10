<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\CollectionItem;

class Controller_CollectionItem extends Controller
{

	function __construct($id)
	{
		$this->model = new CollectionItem($id);
		$this->view = new View();
	}
	
	function action_index($context)
	{
		$data = $this->model->get_data($context['MODE']);
                
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
		$this->view->generate($context, 'template_view.php', $data);
	}
	function action_view($context)
	{
                $this->action_index($context);
	}
	function action_edit($context)
	{
                $this->action_index($context);
	}
	function action_del($context)
	{
		$data = $this->model->get_data($context['MODE']);
                
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/collection_del.php";
		$this->view->generate($context, 'template_view.php', $data);
	}
}
