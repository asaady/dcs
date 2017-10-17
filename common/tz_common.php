<?php
$arPROP_TYPE = array('STR'=>'str',
    'FLOAT'=>'float',
    'INT'=>'int',
    'BOOL'=>'bool',
    'BLOB'=>'blob',
    'ID'=>'id',
    'DATE'=>'date',
    'FILE'=>'file');

    function check_uuid($var)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $var);

    }

