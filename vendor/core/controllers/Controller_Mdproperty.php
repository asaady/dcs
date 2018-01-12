<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Mdproperty;
use Dcs\Vendor\Core\Views\View;


class Controller_Mdproperty extends Controller
{

	function __construct($id)
	{
		$this->model = new Mdproperty($id);
		$this->view = new View();
	}
	
	function action_index($context)
	{
		$data = $this->model->get_data();
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_view.php";
		$this->view->generate($context, $data);
	}
	function action_view($context)
	{
            $this->action_index($context);
        }
        function action_edit($context)
        {
            $this->action_index($context);
        }
}
