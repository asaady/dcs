<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;
class Mdcollection extends Sheet implements I_Sheet, I_Set, I_Property
{
    use T_Sheet;
    use T_MdSet;
    use T_Mdproperty;
    use T_CProperty;
    public static function txtsql_forDetails() 
    {
        return "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, "
                    . "NULL as mdid, '' as mdname, '' as mdsynonym, "
                    . "mdi.name as mdtypename, "
                    . "mdi.synonym as mdtypedescription "
                    . "FROM \"MDTable\" AS mdt "
                        . "INNER JOIN \"CTable\" AS mdi "
                        . "ON mdt.mditem=mdi.id "
                    . "WHERE mdt.id= :id";
    }
    public function head() 
    {
        return new MdentitySet($this->mditem);
    }
    public function item() 
    {
        return new CProperty($this->id);
    }
    public function load_data()
    {
        return NULL;
    }        
    function update($data) 
    {
    }
    function create_property($data) 
    {
    }
    function before_save($data) 
    {
    }
}
