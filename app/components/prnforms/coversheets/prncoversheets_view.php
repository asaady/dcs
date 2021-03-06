<?php
    $PNG_TEMP_DIR = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = '/upload/';

    include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/phpqrcode/qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    $matrixPointSize = 3;
    // user data
    $filename = $PNG_TEMP_DIR.'test'.md5($arResult['ITEMID'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
    QRcode::png($arResult['ITEMID'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    //display generated file
    echo "<div class=\"print_header\">";
    echo "<div class=\"print_img\">";
    echo "<img class=\"alignleft\" src=\"".$PNG_WEB_DIR.basename($filename)."\" />";
    echo "</div>";
    echo "<div class=\"print_col1\">";
    echo "<p class=\"small_text\">Отмывка прокладки _____</p>";
    echo "<p class=\"small_text\">Отмывка оснований ______</p>";
    echo "<p class=\"small_text\">Отмывка крышек ________</p>";
    echo "<p class=\"small_text\">Скрайбирование ________</p>";
    echo "</div>";
    echo "<div class=\"print_col2\">";
    echo "<p class=\"normal_text\">$data[depart]<br>Сопроводительный лист $data[name]</p>";
    echo "<p class=\"small_text\">Номер ________________________<br>&emsp;&emsp;&emsp;&emsp;&emsp;<sup><small>партия оснований</small></sup><br>Номер ________________________<br>&emsp;&emsp;&emsp;&emsp;&emsp;<sup><small>№ с/л пластины</small></sup></p>";
    //echo "<p>Дата запуска $data[date]</p>";
    echo "</div>";
    echo "<div class=\"print_col3\">";
    echo "<p class=\"side_text\">Изделие $data[tprod]</p>";
    echo "<p class=\"small_text\">Процент забракования на<br>отбраковочных электрических испытаниях<br> __________________________</p>";
    echo "</div>";
    echo "</div>";
    //echo "<p class=\"img_text\">Количество запуска $data[start]</p>";
    
    tzVendor\View::outContentToPrint($arResult, $data);
    echo "<div class=\"print_footer\">";
    echo "<div class=\"print_col50\">";
    echo "<p class=\"normal_text\">Нач.цеха (ст.мастер)  _________</p>";
    echo "</div>";
    echo "<div class=\"print_col50\">";
    echo "<p class=\"side_text\">Нач.БТК (ст.контрольный мастер)  ________</p>";
    echo "</div>";
    echo "</div>";
?>
