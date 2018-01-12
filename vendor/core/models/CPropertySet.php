<?php
namespace Dcs\Vendor\Core\Models;

use Exception;

class CPropertySet extends PropertySet
{
    use TProperties;
    use TcProperty;
    
    public function __construct($mdid) 
    {
	if ($mdid=='') 
        {
            throw new Exception("class.CPropertySet constructor: mdid is empty");
	}
        //конструктор базового класса
         parent::__construct($mdid);
         $this->tablename = "CProperties";
    }
}

