<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" >
    <meta name="author" content=<?=DCS_COMPANY_NAME?>>
    <meta name="description" content=<?=DCS_COMPANY_NAME?>>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php 
        echo "<title>$arResult[TITLE]</title>";
    ?>
    <!-- css stylesheets -->
    <link href="/css/normalize.css" rel="stylesheet" type="text/css">
    <link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/css/bootstrap-select.min.css" rel="stylesheet" type="text/css">
    <link href="/css/default.css" id="theme_base" rel="stylesheet">
    <link href="/css/default.date.css" id="theme_date" rel="stylesheet">
    <link href="/css/default.time.css" id="theme_time" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet" type="text/css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body data-spy="scroll" data-target=".navbar" data-offset="50">
    <header>
        <?php
        dcs\vendor\core\View::out_navbar($arResult,$data);
        ?>                        
    </header>    
    <main>
        <div class="container-fluid">  
            <div class="row">
                <div class="col-xs-12 col-md-12">
                    <div class="container-fluid dcs_object">
                        <?php
                        echo "<input class=\"form-control\" name=\"curid\" type=\"hidden\" value=\"$arResult[CURID]\">";
                        echo "<input class=\"form-control\" name=\"version\" type=\"hidden\" value=\"$data[version]\">";
                        echo "<input class=\"form-control\" name=\"page\" type=\"hidden\" value=\"$arResult[PAGE]\">";
                        echo "<input class=\"form-control\" name=\"itemid\" type=\"hidden\" value=\"$arResult[ITEMID]\">";
                        echo "<input class=\"form-control\" name=\"mode\" type=\"hidden\" value=\"$arResult[MODE]\">";
                        echo "<input class=\"form-control\" name=\"action\" type=\"hidden\" value=\"$arResult[ACTION]\">";
                        echo "<input class=\"form-control\" name=\"command\" type=\"hidden\" value=\"\">";
                        echo "<input class=\"form-control\" name=\"filter_id\" type=\"hidden\" value=\"\">";
                        echo "<input class=\"form-control\" name=\"filter_val\" type=\"hidden\" value=\"$arResult[PARAM]\">";
                        echo "<input class=\"form-control\" name=\"filter_min\" type=\"hidden\" value=\"\">";
                        echo "<input class=\"form-control\" name=\"filter_max\" type=\"hidden\" value=\"\">";
                        echo "<input class=\"form-control\" name=\"sort_id\" type=\"hidden\" value=\"\">";
                        echo "<input class=\"form-control\" name=\"sort_dir\" type=\"hidden\" value=\"\">";
                        include $arResult['content'];
                        ?>
                        <br class="clearfix" />
                    </div>    
                </div>        
            </div>            
        </div>        
        <div id="ivalue" class="input-group"></div>
        <div id="form_result"></div>
        <?php
        include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/modal_win_template.php";    
        ?>
        <div id="loader">
            <img  style="display: none;" width="10" height="10" alt="loading" src="data:image/gif;base64,R0lGODlhEAAQAPIAAP///zqHrc/h6mylwjqHrYW0zJ7D1qrL2yH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==" />
        </div>    
    </main>    
    <footer>
        <div class="container-fluid">
            <div class="row">
                <a href="/">Copyright &copy; <?=DCS_COMPANY_NAME?> 2017.</a>
            </div>    
        </div>
    </footer>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="/js/jquery-3.2.1.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>    
        <script src="/js/moment.js"></script>
<?php
        if (($arResult['ACTION']=='EDIT')||($arResult['ACTION']=='CREATE'))
        {
            echo "<script src=\"/js/picker.js\"></script>";
            echo "<script src=\"/js/picker.date.js\"></script>";
            echo "<script src=\"/js/picker.time.js\"></script>";
        }
?>
        <script type="text/javascript">
        //<![CDATA[
        function logout()
        {
            $("input[name='command']").val('logout');
            $("input[name='mode']").val('AUTH'); 
            var data = $('.row :input').serializeArray();
            $.ajax(
            {
                url: '/common/post_ajax.php',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(result) {
                    location.href=result['redirect'];
                }
            })      
        };

<?php
        if (($arResult['ACTION']=='EDIT')||($arResult['ACTION']=='CREATE'))
        {
            echo "$('input.form-control[it=date]').pickadate({
                    selectMonths: true,
                    format: 'yyyy-mm-dd',
                    formatSubmit: 'yyyy-mm-dd'
                  });";
        }        
        if (array_key_exists('jscript', $arResult))
        {
            include $arResult['jscript'];
        }        
        else
        {
            include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/js/core_app.js";
        }    
?>
        </script>
        
    </body>
</html>