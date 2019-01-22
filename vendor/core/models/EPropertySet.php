<?php
namespace Dcs\Vendor\Core\Models;

class EPropertySet {
    use T_Sheet;
    
    public function head() {
        return new Mdentity($this->mdid);
    }

    public function item($id) {
        return new EProperty($id);
    }

    public function getprop_classname()
    {
        return NULL;
    }
}
