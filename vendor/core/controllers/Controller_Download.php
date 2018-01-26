<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Download;


class Controller_Download extends Controller
{

    function __construct($id)
    {
            $this->model = new Download($id);
    }

    function action_index($context)
    {
            return $this->model->get_data($context['CURID']);
    }
    function action_error($context)
    {
        header('HTTP/1.1 404 Not Found');
        header("Error: 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }
    function action_denyaccess($context)
    {
        echo json_encode(array('msg'=>'Deny access'));
    }
}
