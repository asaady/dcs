<?php
namespace tzVendor;

class Entity_View extends View
{

	function generate($arResult, $template_view, $data = null)
	{
                
            include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/".$template_view;
	}
}
