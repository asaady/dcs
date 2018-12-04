<?php
namespace Dcs\Vendor\Core\Models;

interface I_Item
{
    public function set_data($data);
    public function save_new();
    public function update_properties($context,$data,$n=0);     
    public function update_dependent_properties($context,$data);
    public function before_save($context,$data);
}
