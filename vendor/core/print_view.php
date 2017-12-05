<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" >
    <meta name="author" content=<?=TZ_COMPANY_NAME?>>
    <meta name="description" content=<?=TZ_COMPANY_NAME?>>
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
<body style="padding-top: 5px">
    <main>
        <div class="row" name="tzobject">
            <div class="container-fluid">  
                <div class="row">
                    <div class="col-xs-12 col-md-12">
               `        <?php
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
                    </div>        
                </div>            
            </div>        
        </div>                    
    </main>    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>    
    <script src="/js/moment.js"></script>
    <script src="/js/picker.js"></script>
    <script src="/js/picker.date.js"></script>
    <script src="/js/picker.time.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="/js/ajax_form.js"></script>
    <script type="text/javascript">
    //<![CDATA[
    <?php
    if (array_key_exists('jscript', $arResult))
    {
        include $arResult['jscript'];
    }        
    ?>
    </script>
</body>
</html>