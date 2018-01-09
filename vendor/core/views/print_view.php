<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" >
    <meta name="author" content=<?=DCS_COMPANY_NAME?>>
    <meta name="description" content=<?=DCS_COMPANY_NAME?>>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" >
    <?php 
        echo "<title>$arResult[TITLE]</title>";
    ?>
    <!-- css stylesheets -->
    <link href="/css/style.css" rel="stylesheet" type="text/css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="toprint">
    <main>
<?php
        echo "<div class=\"row\" name=\"tzobject\">";
         echo "<input name=\"curid\" type=\"hidden\" value=\"$arResult[CURID]\">";
         echo "<input name=\"version\" type=\"hidden\" value=\"$data[version]\">";
         echo "<input name=\"page\" type=\"hidden\" value=\"$arResult[PAGE]\">";
         echo "<input name=\"itemid\" type=\"hidden\" value=\"$arResult[ITEMID]\">";
         echo "<input name=\"mode\" type=\"hidden\" value=\"$arResult[MODE]\">";
         echo "<input name=\"action\" type=\"hidden\" value=\"$arResult[ACTION]\">";
         echo "<input name=\"command\" type=\"hidden\" value=\"\">";
         echo "<input name=\"filter_id\" type=\"hidden\" value=\"\">";
         echo "<input name=\"filter_val\" type=\"hidden\" value=\"$arResult[PARAM]\">";
         echo "<input name=\"filter_min\" type=\"hidden\" value=\"\">";
         echo "<input name=\"filter_max\" type=\"hidden\" value=\"\">";
         echo "<input name=\"sort_id\" type=\"hidden\" value=\"\">";
         echo "<input name=\"sort_dir\" type=\"hidden\" value=\"\">";
        echo "</div>";
         include $arResult['content'];
         ?>
    </main>    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/js/jquery-3.2.1.min.js"></script>
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