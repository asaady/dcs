<?php
namespace Dcs\Vendor\Core\Views;

class Error_View extends View implements I_View
{
    use T_View;
    
    public function item_view($data)
    {
        return $this->template->error_view($data);
    }        
}
