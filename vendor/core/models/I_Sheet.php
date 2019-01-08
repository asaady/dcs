<?php
namespace Dcs\Vendor\Core\Models;

interface I_Sheet
{
    public function getplist($context);
    public function getsets($context);
    public function getitemplist();
    public function txtsql_forDetails();
    public function getNameFromData($context,$data='');
    public function get_data(&$context);
    public function item($id);
    public function head();
    public function getprop_classname();
    public function item_classname();
    public function load_data($context,$data='');
    public function getItemsByFilter($context);
    public function getItemsByName($name);
    public function getItems($context);
    public function getItemsProp($context);
    public static function txtsql_access();  
    public function setnamesynonym();
    public function getaccessright_id();
    public function create_object($name,$synonym='');
    public function loadProperties();
    public function get_select_properties($strwhere);
    public function txtsql_property($parname);
    public function txtsql_properties($parname);
    public function getProperties($byid = FALSE, $filter = '');
    public function after_choice($context,$data);
}
