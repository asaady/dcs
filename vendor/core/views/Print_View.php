<?php
namespace Dcs\Vendor\Core\Views;
class Print_View extends View implements I_View 
{
    use T_View;
    public function body_header_view($data)
    {
        return "<p class=\"dcs-printform-title\">$data[name]</p>\n";
    }
    public function body_content_view($data)
    {
        return $this->template->get_body_toprint_content($this->context, $data);
    }        
    public function body_footer_view()
    {
        return '';
    }        
    public function body_script_view()
    {
        return $this->template->get_body_script_toprint();
    }   
}
