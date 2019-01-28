<?php
namespace Dcs\Vendor\Core\Views;

interface I_View 
{
    public function generate($data = null);
    public function body_content_view($data);
    public function body_script_view($data);
    public function body_header_view($data);
    public function body_footer_view();
    public function body_loader_view();
    public function body_modalform_view();
    public function body_result_view();
    public function body_ivalue_view();
    public function body_context_view($data);
    public function body_actionlist_view();
    public function head_view();
}
