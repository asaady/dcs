<?php
namespace Dcs\Vendor\Core\Views;

class Error_View extends View implements I_View
{
    use T_View;
    
    public function body_content_view($data)
    {
        return $this->template->error_view($data);
    }        
}
