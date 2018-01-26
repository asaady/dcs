<?php
namespace Dcs\Vendor\Core\Views;
class Denyaccess_View extends View implements I_View {
    use T_View;
    public function generate($data = null)
    {
        echo "<!DOCTYPE html>";
        echo "<html lang=\"ru\">";
        echo "<head>";
            $this->head_view();
        echo "</head>";
        echo "<body>";    
            echo "<main>";
                $this->body_main_view($data);
            echo "</main>";
            $this->body_script_view();
        echo "</body>";
        echo "</html>";
    }
    public function body_main_view($data)
    {
        $this->content_view($data);
    }
    public function content_view($data) 
    {
        echo "<div class=\"container\">";
        echo "<div class=\"row-fluid\">";
        $this->item_view($data);
        echo "<br class=\"clearfix\" />";
        echo "</div>"; 
        echo "</div>"; 
    }
    public function item_view($data)
    {
        echo "<div class=\"row\">";
        echo "<h1>Ошибка доступа к данным</h1>";
        echo "<h3>Доступ запрещен</h3>";
        echo "<a href=\"/\">на главную</a>";
        echo "</div>";
    }        
}
