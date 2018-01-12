<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Mdproperty;
use Dcs\Vendor\Core\Models\Cproperty;
use Dcs\Vendor\Core\Models\Mdentity;
use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\EntitySet;
use Dcs\Vendor\Core\Models\Entity;

class Controller_Mdentity extends Controller
{

	function __construct($mdid)
	{
            $this->model = new Mdentity($mdid);
            $this->view = new View();
	}
	
	function action_index($context)
	{
            $data = $this->model->getPropData($context['MODE'],$context['ACTION']);
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
	function action_create_old($context)
	{
            if ($this->model->getmdtypename()=='Cols')
            {
                $entity = new dcs\vendor\core\CollectionItem($this->model->getid());
                $data = $entity->get_data($context['MODE']);
                $context['ITEMID'] = $this->model->getid();
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_view.php";
                $this->view->generate($context, $data);
            }   
            else 
            {
                $entity = new Entity($this->model->getid());
                $data = $entity->get_data($context['MODE']);
                $context['ITEMID'] = $this->model->getid();
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/item_view.php";
                $this->view->generate($context, $data);
            }
	}
        function action_create($context) 
        {
            if (($this->model->getmdtypename()=='Cols')||($this->model->getmdtypename()=='Comps'))
            {
                $model = new Cproperty($context['ITEMID']);
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/cproperty_view.php";
            }   
            elseif ($this->model->getmdtypename()=='Regs')
            {
                $model = new Rproperty($context['ITEMID']);
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/cproperty_view.php";
            }   
            else
            {
                $model = new Mdproperty($context['ITEMID']);
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/mdproperty_view.php";
            }    
            $data = $model->get_data();

            $this->view->generate($context, $data);
        }
}
