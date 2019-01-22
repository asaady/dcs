<?php
namespace Dcs\Vendor\Core\Models;

interface I_Sheet
{
    public function after_choice();
    public function before_save($data='');
    public function params_to_create($data='');
    public function create_object($params);
    public function after_create();
    public function get_data();
//    public function get_select_properties($strwhere);
    public function getArrayNew($newobj);    
    public function dbtablename();
    public function get_items();
    public function getItems($filter=array());
    public function getItemsByFilter();
    public function getItemsByName($name);
    public function getItemsProp();
    public function getListItemsByFilter($filter=array()); 
    public function getNameFromData($data='');
    public function getProperties($byid = FALSE, $filter = '');
    public function getaccessright_id();
    public function getitemplist();
    public function getplist();
    public function getprop_classname();
    public function getsets();
    public function item($id);
    public function item_classname();
    public function head();
    public function load_data($data='');
    public function loadProperties();
    public function setnamesynonym();
    public static function txtsql_access();  
    public function txtsql_forDetails();
    public function txtsql_properties($parname);
    public function txtsql_property($parname);
}
