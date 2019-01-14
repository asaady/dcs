<?php
namespace Dcs\Vendor\Core\Models;

interface I_Item
{
    public function set_data($data);
    public function update_properties($data,$n=0);     
    public function update_dependent_properties($data);
    public function getArrayNew($newobj);
}
