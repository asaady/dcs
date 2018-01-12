<?php
namespace Dcs\Vendor\Core\Models;

class MdpropertySet extends Head implements I_Head, I_Property
{
    use T_Properties;
    use T_EProperty;
    use T_EItem;
    
    public function __construct($mdid='') 
    {
	if ($mdid=='') 
        {
            throw new Exception("class.MDPropertySet constructor: mdid is empty");
	}
            //конструктор базового класса
         parent::__construct($mdid);
         $this->tablename = "MDProperties";
    }
    public function get_item() {
        return new Mdproperty($this->id);
    }
}

