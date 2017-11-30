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
<body>
    <header>
        <div class="container-fluid">
            <div class="navbar navbar-fixed-top navbar-default" role="navigation">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#b-menu-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="/"><?=TZ_COMPANY_NAME?></a>
                    </div>
                    <div class="collapse navbar-collapse" id="b-menu-1">
                        <ul class="nav navbar-nav pull-right">
                    <?php
                        if (tzVendor\User::isAuthorized())
                        {    
                            foreach($arResult['MENU'] as $ct)
                            {    
                              echo "<li><a href=\"$arResult[PREFIX]/$ct[ID]\">$ct[SYNONYM]</a></li>";
                            }
                            if (\tzVendor\User::isAdmin()&&($arResult['MODE']!=='CONFIG'))
                            {    
                                echo "<li>";
                                echo "<a href=\"/config\">";
                                echo "<i class=\"material-icons\">settings</i>";
                                echo "</a>";    
                                echo "</li>";
                            }
                            echo "<li class=\"dropdown\">";
                            echo "<a href=# class=\"dropdown-toggle\" data-toggle=\"dropdown\">";
                            echo "<i class=\"material-icons\">account_box</i>";
                            echo "<b class=\"caret\"></b>";
                            echo "</a>";
                            echo "<ul class=\"dropdown-menu\">";
                            echo "<li><a href=/6accfac4-dc22-4d12-985b-946d3a61bbd1>Настройки</a></li>";
                            echo "<li><a href=javascript:logout()>Выход</a></li>";
                            echo "</ul>";
                            echo "</li>";
                        }        
                    ?>                        
                        </ul>
                    </div> <!-- /.nav-collapse -->
                    <div class="navbar-inner">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <ol class="breadcrumb">
                                <?php
                                        foreach($data['navlist'] as $key=>$val)
                                        {    
                                          echo "<li><a href=\"$arResult[PREFIX]/$key\">$val</a></li>";
                                        }
                                ?>                        
                                 </ol>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                            <ul class="nav nav-tabs pull-right" id="actionlist">
                               <li></li>  
                            </ul>
                            </div>            
                        </div>        
                    </div> 
                </div> <!-- /.container -->
            </div> <!-- /.navbar -->
        </div> <!-- /.container -->
        </header>    
         <main>
            <div class="container-fluid">  
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="container-fluid">  
                            <div class="row" name="tzobject">
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
                            include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/modal_win_template.php";    
                            include $arResult['content'];
                            ?>
                            </div>     
                            <br class="clearfix" />
                        </div>    
                    </div>        
                </div>            
            </div>        
            <div id="ivalue" class="ivalue-block"></div>
            <div id="form_result"></div>
        </main>    
	<footer>
		<div class="container-fluid">
                    <div class="row">
        		<a href="/">Copyright &copy; <?=TZ_COMPANY_NAME?> 2017.</a>
                    </div>    
		</div>
	</footer>
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
        function logout()
        {
            var $data = {'act':'logout'};
            $.ajax(
            {
                url: '/common/AuthorizationAjaxRequest.php',
                type: 'post',
                data: $data,
                success: function(result) {
                    location.href='/';
                }
            })      
        };
<?php
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