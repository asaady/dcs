<?php
namespace Dcs\Vendor\Core\Views;

trait T_View {
    public function generate($data = null)
    {
        echo "<!DOCTYPE html>";
        echo "<html lang=\"ru\">";
        echo "<head>";
            $this->head_view();
        echo "</head>";
        echo "<body data-spy=\"scroll\">";    
            echo "<header data-target=\"#dcs-nav\" data-offset=\"50\">";
                $this->body_header_view($data);
            echo "</header>";
            echo "<main>";
                echo "<nav id=\"dcs-nav\" class=\"navbar\" data-spy=\"affix\" data-offset-top=\"150\">
                            <ul class=\"nav nav-tabs pull-right\" id=\"actionlist\"><li></li></ul>
                     </nav>";
                $this->body_main_view($data);
            echo "</main>";
            echo "<footer>";
                $this->body_footer_view();
            echo "</footer>";
            $this->body_script_view();
        echo "</body>";
        echo "</html>";
    }
}
