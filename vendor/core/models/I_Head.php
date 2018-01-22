<?php
namespace Dcs\Vendor\Core\Models;

interface I_Head
{
    public function item();
    public function load_data();
    public function getItemsByFilter($context, $filter);
    public function getItemsByName($name);
 }
