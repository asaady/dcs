<?php

namespace Dcs\Vendor\Core\Views;

interface I_Template {
    public function get_head($context);
    public function get_body_header($context, $data);
    public function get_body_action_list();
    public function get_body_context($context, $data);
    public function get_body_content($context, $data);
    public function get_body_toprint_content($context, $data);
    public function get_body_ivalue();
    public function get_body_form_result();
    public function get_body_modal_form();
    public function get_body_loader_form();
    public function get_body_footer();
    public function get_body_script($context);
    public function get_body_script_toprint();
    public function auth_view();
    public function error_view($data);
}
