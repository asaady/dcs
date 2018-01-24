<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class EProperty extends Sheet implements I_Sheet, I_Property 
{
    use T_Sheet;
    use T_Item;
    use T_Mdproperty;
    use T_EProperty;
    
    public function txtsql_forDetails() 
    {
        return "SELECT mp.id, mp.mdid, mp.name, mp.synonym, "
                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                . "FROM \"MDProperties\" as mp "
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
}
