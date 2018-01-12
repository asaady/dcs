<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\EntitySet;
use Dcs\Vendor\Core\Models\Entity;
use Dcs\Vendor\Core\Models\CollectionItem;

class Controller_EntitySet extends Controller
{

	function __construct($id)
	{
		$this->model = new EntitySet($id);
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
	function action_edit($context)
        {
            $this->action_index($context);
        }
	function action_create($context)
	{
            if (($this->model->getmditem()->getname()=='Cols')||($this->model->getmditem()->getname()=='Comps'))    
            {
                $entity = new CollectionItem($this->model->getid());
		$data = $entity->get_data($context['MODE']);
                $context['ITEMID'] = $this->model->getid();
            }   
            else
            {    
                $entity = new Entity($this->model->getid());
		$data = $entity->get_data($context['MODE']);
                $context['ITEMID'] = $this->model->getid();
            }    
            $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_view.php";
            $this->view->generate($context, $data);
	}
}

