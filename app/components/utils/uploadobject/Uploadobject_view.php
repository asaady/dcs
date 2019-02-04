<?php
namespace Dcs\App\Components\Utils\Uploadobject;


use Exception;
use Dcs\Vendor\Core\Views\Template;
use Dcs\Vendor\Core\Views\View;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class Uploadobject_view extends View
{
    use \Dcs\Vendor\Core\Views\T_View;
    
    protected $mode;
    protected $action;
    protected $views_path;
    protected $template;
    
    function __construct() {
        $this->views_path = "/app/components/utils/uploadobject";
        $this->template = new Uploadobject_template();
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
