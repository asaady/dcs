<?php
namespace Dcs\Vendor\Core\Views;

interface I_View 
{
    public function head_view();
    public function body_header_view($data);
    public function body_main_view($data);
    public function body_footer_view();
    public function body_script_view();
}
