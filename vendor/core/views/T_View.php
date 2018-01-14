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
    public function head_view()
    {
        echo "<meta charset=\"utf-8\">";
        echo "<meta name=\"author\" content=\"".DCS_COMPANY_NAME."\">";
        echo "<meta name=\"description\" content=\"".DCS_COMPANY_NAME."\">";
        echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\">";
        echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">";
        echo "<title>".$this->context['TITLE']."</title>";
        echo "<!-- css stylesheets -->";
        echo "<link href=\"/public/css/normalize.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/bootstrap-select.min.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/default.css\" id=\"theme_base\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/default.date.css\" id=\"theme_date\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/default.time.css\" id=\"theme_time\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/style.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->";
        echo "<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->";
        echo "<!--[if lt IE 9]>";
        echo "<script src=\"https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js\"></script>";
        echo "<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>";
        echo "<![endif]-->";
    }        
    public function body_header_view($data)
    {
        $this->out_navbar($data);
    }
    public function body_main_view($data)
    {
        $this->context_view($data);
        echo "<div id=\"ivalue\" class=\"input-group\"></div>";
        echo "<div id=\"form_result\"></div>";
        $this->modal_view();    
        echo "<div id=\"loader\">";
        echo "<img  style=\"display: none;\" width=\"10\" height=\"10\" "
                    . "alt=\"loading\" src=\"data:image/gif;base64,R0lGODlhEAAQAPIAAP///zqHrc/h6mylwjqHrYW0zJ7D1qrL2yH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==\" />";
        echo "</div>";    
    }
    public function body_footer_view()
    {
        echo "<div class=\"container-fluid\">";
        echo "<div class=\"row\"><a href=\"/\">Copyright &copy;".DCS_COMPANY_NAME." 2017.</a></div>";
        echo "</div>";
    }
    public function body_script_view()
    {
        echo "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->";
        echo "<script src=\"/public/js/jquery-3.2.1.min.js\"></script>";
        echo "<script src=\"/public/js/bootstrap.min.js\"></script>";
        echo "<script src=\"/public/js/moment.js\"></script>";
        echo "<script src=\"/public/js/core_app.js\"></script>";
        if (($this->context['ACTION'] == 'EDIT')||
            ($this->context['ACTION'] == 'CREATE')) {
            echo "<script src=\"/public/js/picker.js\"></script>";
            echo "<script src=\"/public/js/picker.date.js\"></script>";
            echo "<script src=\"/public/js/picker.time.js\"></script>";
        }
    }        
    public function modal_view()
    {
        echo "<div id=\"dcsModal\" class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"dcsModalLabel\" aria-hidden=\"true\">";
        echo "<div class=\"modal-dialog\">";
        echo "<div class=\"modal-content\">";
            echo "<div class=\"modal-header\">";
            echo "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>";
            echo "<h4 class=\"modal-title\" id=\"dcsModalLabel\">Saving the modified data</h4>";
            echo "</div>";
            echo "<div class=\"modal-body\">";
                echo "<table class=\"table table-border\">";
                    echo "<caption></caption>";
                    echo "<thead id=\"modalhead\">";
                    echo "<tr><th id=\"name\">Props</th><th id=\"prev\">Prev.value</th><th id=\"value\">new value</th></tr>";
                    echo "</thead>"; 
                    echo "<tbody id=\"modallist\"><tr></tr></tbody>";
                echo "</table>";
            echo "</div>";
            echo "<div class=\"modal-footer\">";
                echo "<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Закрыть</button>";
                echo "<button type=\"button\" id=\"dcsModalOK\" class=\"btn btn-primary\">OK</button>";
            echo "</div>";
        echo "</div><!-- /.modal-content -->";
        echo "</div><!-- /.modal-dialog -->";
        echo "</div><!-- /.modal -->";
    }        
    public function context_view($data)
    {
        echo "<div class=\"container-fluid\">";
        echo "<div class=\"row\">";
        echo "<div class=\"col-xs-12 col-md-12\">";
        echo "<input class=\"form-control\" name=\"curid\" type=\"hidden\" value=\"".$this->context['CURID']."\">";
        echo "<input class=\"form-control\" name=\"version\" type=\"hidden\" value=\"".$data['version']."\">";
        echo "<input class=\"form-control\" name=\"page\" type=\"hidden\" value=\"".$this->context['PAGE']."\">";
        echo "<input class=\"form-control\" name=\"itemid\" type=\"hidden\" value=\"".$this->context['ITEMID']."\">";
        echo "<input class=\"form-control\" name=\"mode\" type=\"hidden\" value=\"".$this->context['MODE']."\">";
        echo "<input class=\"form-control\" name=\"action\" type=\"hidden\" value=\"".$this->context['ACTION']."\">";
        echo "<input class=\"form-control\" name=\"command\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_id\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_val\" type=\"hidden\" value=\"".$this->context['PARAM']."\">";
        echo "<input class=\"form-control\" name=\"filter_min\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_max\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"sort_id\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"sort_dir\" type=\"hidden\" value=\"\">";
        $this->item_view($data);
        echo "<br class=\"clearfix\" />";
        echo "</div>"; 
        echo "</div>"; 
        echo "</div>"; 
    }        
}
