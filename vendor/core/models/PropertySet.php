<?php
namespace Dcs\Vendor\Core\Models;
abstract class PropertySet extends Sheet implements I_Sheet, I_Set {
    public function __construct($id)
    {
        if ($id === '') {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        $this->id = $id;
        $this->isnew = false;
        $this->mdid = $id;
        $this->head = $this->get_head();
        $this->properties = $this->loadProperties();
        $this->data = array();
        $this->version = time();
        
    }
    
}
