<?php
namespace Dcs\Vendor\Core\Models;

interface I_Head
{
    public function get_item();
    public function getItemsByFilter($context, $filter);
    public function getItemsByName($name);
 }
