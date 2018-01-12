<?php
namespace Dcs\Vendor\Core\Controller;

class Controller_404 extends Controller
{
	function __construct($id='')
	{
		$this->view = new View();
	}
	function action_index($context)
	{
                $data = array();
                $data['id'] = '';
                $data['version'] = time();
                $data['navlist']=array();
                $data['actionlist']=array();
                $data['plist']=array();
                $context['content'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/404_view.php"; 
		$this->view->generate($context,$data);
	}
}
