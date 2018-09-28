<?php
namespace Dcs\Vendor\Core\Views;

use Exception;
use Dcs\Vendor\Core\Views\Template;
use Dcs\App\Templates\Default_Template;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class View implements I_View
{
    use T_View;
    
    protected $mode;
    protected $action;
    protected $views_path;
    protected $template;
    protected $context;
    
    function __construct() {
        $this->views_path = "/vendor/core/views";
        $this->template = new Default_Template();
        $this->context = array();
    }
    public function setcontext($context) 
    {
        $this->context = $context;
    }

    public function getcontextdata($context) 
    {
        return $this->context;
    }
    
    public function set_template_view($val) 
    {
        $this->template_view = $val;
    }
    public function get_template_view() 
    {
        return $this->template_view;
    }
    public function set_views_path($val) 
    {
        $this->views_path = $val;
    }
    public function get_views_path() 
    {
        return $this->views_path;
    }

    public function getmode()
    {
        return $this->mode;
    }
    public function setmode($val)
    {
        $this->mode = $val;
    }
    public function setaction($val)
    {
        $this->action = $val;
    }
    public function setclass($data, $mode = '', $edit_mode = '')
    {
        $idclass = 'hidden';
        if ($mode === 'CONFIG')
        {    
            $idclass = 'readonly';
        }
        $class = 'active';
        if ($edit_mode === 'EDIT')
        {    
            $class = 'readonly';
        }
        foreach ($data as $key=>$val) {
            if ($val['class'] !== '')
            {
                continue;
            }    
            if ($key == 'id') {
                $val['class'] = $idclass;
                continue;
            }
            $val['class'] = $class;
        }
        return $data; 
    }        
}
