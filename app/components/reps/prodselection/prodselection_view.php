<?php
if ($arResult['MODE']=='PRINT')
{    
    echo "<p id=\"print-title\">$data[TITLE]</p>";
    tzVendor\View::outContentToPrint($arResult, $data);
}
else 
{
    tzVendor\View::outContent($arResult, $data);
}
?>
