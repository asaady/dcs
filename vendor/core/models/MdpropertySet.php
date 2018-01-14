<?php
namespace Dcs\Vendor\Core\Models;

class MdpropertySet extends Head implements I_Head
{
    use T_Head;
    
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
    public function getItemsByFilter($context, $filter) 
    {
        
    }
    public function getItemsByName($name)
    {
        
    }        
}

