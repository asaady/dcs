<?php
namespace Dcs\Vendor\Core\Models;

interface I_Sheet
{
    public function txtsql_forDetails();
    public function item();
    public function head();
    public function load_data();
    public function getItemsByFilter($context);
    public function getItemsByName($name);
 }
