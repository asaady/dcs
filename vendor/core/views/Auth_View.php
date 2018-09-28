<?php
namespace Dcs\Vendor\Core\Views;

use Dcs\Vendor\Core\Models\User;

class Auth_View extends View implements I_View
{
    use T_View;
    
    public function item_view($data)
    {
        return $this->template->auth_view();
    }        
}