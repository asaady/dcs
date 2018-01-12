<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Entity;
use Dcs\Vendor\Core\Views\View;

class Controller_Entity extends Controller
{

	function __construct($id)
	{
		$this->model = new Entity($id);
		$this->view = new View();
	}
	
	function action_index($context)
	{
            $data = $this->model->get_data($context['MODE']);
            if ($context['MODE'] == 'PRINT') 
            {
                $context['content'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_print.php";
                $context['jscript'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/public/js/core_app.js";
                $this->view->set_template_view(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/core/views/print_view.php");
		$this->view->generate($context, $data);
            }
            else
            {    
                if ($this->model->getmdentity()->getmdtypename()=='Vals')
                {
                    $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/public/main.html";
                }
                else 
                {
                    $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_view.php";
                }    
		$this->view->generate($context, $data);
            }    
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
