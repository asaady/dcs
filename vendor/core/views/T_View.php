<?php
namespace Dcs\Vendor\Core\Views;

trait T_View {
    public function generate($data = null)
    {
        $skelet = "<!DOCTYPE html>\n"
                . "<html lang=\"ru\">\n"
                . "<head>\n"
                . "<!--head-->"
                . "</head>\n"
                . "<body>\n"
                . "<header>\n"
                . "<!--body_header-->"
                . "</header>\n"
                . "<main>\n"
                . "<!--body_action_list-->"
                . "<div class=\"container\">\n"
                . "<!--body_context-->"
                . "<!--body_content-->"
                . "<br class=\"clearfix\" />\n"
                . "</div>\n"
                . "<!--body_ivalue-->"
                . "<!--body_form_result-->"
                . "<!--body_modal_form-->"
                . "<!--body_loader_form-->"
                . "</main>\n"
                . "<footer>\n"
                . "<!--body_footer-->"
                . "</footer>\n"
                . "<!--body_script-->"
                . "</body>\n"
                . "</html>";
        $skelet = str_replace("<!--head-->", $this->head_view(), $skelet);
        $skelet = str_replace("<!--body_header-->", $this->body_header_view($data), $skelet);
        $skelet = str_replace("<!--body_action_list-->", $this->body_actionlist_view() , $skelet);
        $skelet = str_replace("<!--body_context-->", $this->body_context_view($data), $skelet);
        $skelet = str_replace("<!--body_content-->", $this->body_content_view($data), $skelet);
        $skelet = str_replace("<!--body_ivalue-->", $this->body_ivalue_view() , $skelet);
        $skelet = str_replace("<!--body_form_result-->",$this->body_result_view(), $skelet);
        $skelet = str_replace("<!--body_modal_form-->", $this->body_modalform_view(), $skelet);
        $skelet = str_replace("<!--body_loader_form-->", $this->body_loader_view(), $skelet);
        $skelet = str_replace("<!--body_footer-->", $this->body_footer_view(), $skelet);
        $skelet = str_replace("<!--body_script-->", $this->body_script_view($data), $skelet);
        echo $skelet;
    }
    public function item_view($data)
    {
        return $this->template->get_body_content($data);
    }        
    public function body_script_view($data)
    {
        return $this->template->get_body_script($data);
    }   
    public function body_header_view($data)
    {
        return $this->template->get_body_header($data);
    }
    public function body_footer_view()
    {
        return $this->template->get_body_footer();
    }        
    public function body_loader_view()
    {        
        return $this->template->get_body_loader_form();
    }
    public function body_modalform_view()
    {        
        return $this->template->get_body_modal_form();
    }
    public function body_result_view()
    {        
        return $this->template->get_body_form_result();
    }
    public function body_ivalue_view()
    {        
        return $this->template->get_body_ivalue();
    }
    public function body_context_view($data)
    {        
        return $this->template->get_body_context($data);
    }
    public function body_content_view($data)
    {        
        return $this->template->get_body_content($data);
    }
    public function body_actionlist_view()
    {        
        return $this->template->get_body_action_list();
    }
    public function head_view()
    {        
        return $this->template->get_head();
    }
}
