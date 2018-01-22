<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class EProperty extends Head implements I_Head, I_Property 
{
    use T_Head;
    use T_Item;
    use T_EProperty;
    
    public function loadProperties()
    {
        return $this->getplist();
    }
    function item() 
    {
        return NULL;
    }
    public function get_tt_sql_data() 
    {
        $artemptable = array();
        $sql = DataManager::get_select_properties(" where mp.id = :id ");
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->mdid));   
        return $artemptable;
    }
    public function txtsql_getproperties()
    {
        return "";
    }
}
