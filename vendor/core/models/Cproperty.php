<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class Cproperty extends Sheet implements I_Sheet, I_Property 
{
    use T_Sheet;
    use T_Item;
    public function txtsql_forDetails() 
    {
        return "SELECT mp.id, mp.mdid, mp.name, mp.synonym, "
                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                . "FROM \"CProperties\" as mp "
                    . "INNER JOIN \"MDTable\" as mc "
                        . "INNER JOIN \"CTable\" as tp "
                        . "ON mc.mditem = tp.id "
                    . "ON mp.mdid = mc.id "
                . "WHERE mp.id=:id";
    }        
    public function head() 
    {
        return new Mdentity($this->mdid);
    }
    public function item($id='')
    {
        return NULL;
    }        
    public function getprop_classname()
    {
        return NULL;
    }
    public function item_classname()
    {
        return NULL;
    }        
    public function create_object($id,$mdid,$name,$synonym='')
    {
        return NULL;
    }        
    public function getNameFromData($context,$data='')
    {
        return $this->synonym;
    }        
    public function txtsql_property($parname)
    {
        return NULL;
    }        
    public function txtsql_properties($parname)
    {
        return NULL;
    }        
}

