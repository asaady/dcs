<?php
namespace Dcs\Vendor\Core\Models;

interface I_Sheet
{
    public static function txtsql_forDetails();
    public function item();
    public function head();
    public function load_data($context);
    public function getItemsByFilter($context);
    public function getItemsByName($name);
    public static function txtsql_access();  
    public function setnamesynonym();
    public function getaccessright_id();
}
