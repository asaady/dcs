<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;
class Mdcollection extends Head implements I_Head, I_Property
{
    use T_Head;
    use T_Collection;
    use T_Mdproperty;
    use T_CProperty;
    
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
