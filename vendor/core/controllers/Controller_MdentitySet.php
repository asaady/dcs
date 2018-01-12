<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\MdentitySet;
use Dcs\Vendor\Core\Models\Mdentity;

class Controller_MdentitySet extends Controller
{

	function __construct($mditem)
	{
		$this->model = new MdentitySet($mditem);
		$this->view = new View();
	}
	
	function action_index($context)
	{
		$data = $this->model->get_data($context['MODE']);
                $this->view->setcontext($context);
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
	function action_create($context)
	{
            $mdentity = new Mdentity($this->model->getid());
            $data = $mdentity->get_data($context['MODE']);
            $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/mdentity_view.php";
            $this->view->generate($context, $data);
	}
}

