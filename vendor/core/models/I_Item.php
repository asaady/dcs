<?php
namespace Dcs\Vendor\Core\Models;

interface I_Item
{
    public function set_data($data);
    public function save_new();
    public function update_properties($data);
    public function update_dependent_properties($objs);
}
