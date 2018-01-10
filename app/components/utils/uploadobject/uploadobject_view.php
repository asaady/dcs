<?php
if ($arResult['MODE']=='PRINT')
{    
    echo "<p id=\"print-title\">$data[TITLE]</p>";
    dcs\vendor\core\View::outContentToPrint($arResult, $data);
}
else 
{
    dcs\vendor\core\View::outContent($arResult, $data);
}
?>
