<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");

class Common_data {
    protected $arPROP_TYPE;
    public function __construct() {
    $this->arPROP_TYPE = array('STR'=>'str',
                                'FLOAT'=>'float',
                                'INT'=>'int',
                                'BOOL'=>'bool',
                                'TEXT'=>'text',
                                'ID'=>'id',
                                'DATE'=>'date',
                                'FILE'=>'file');
    }
    
    static function check_uuid($var)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $var);

    }
    static function type_to_db($type)
    {
        $res = 'varchar(200)';
        switch ($type) 
        {
            case 'int': $res = 'integer';
                        break;
            case 'float': $res = 'double precision';
                        break;
            case 'date': $res = 'timestamp with time zone';
                        break;
            case 'bool': $res = 'boolean';
                        break;
            case 'text': $res = 'text';
                        break;
            case 'cid': $res = 'uuid';
                        break;
        }
        return $res;
    }
}