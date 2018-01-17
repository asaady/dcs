<?php
namespace Dcs\Vendor\Core\Views;

class C404_View extends View implements I_View
{
    use T_View;
    
    public function item_view($data)
    {
        echo "<div class=\"row\">";
        echo "<h1>Страница не найдена</h1>";
        echo "<h3>Страница устарела, была удалена или не существовала вовсе</h3>";
        echo "<a href=\"/\">на главную</a>";
        echo "</div>";
    }        
}
