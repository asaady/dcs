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
    public static function import_log($string)
    {
        $log_file_name = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).TZ_UPLOAD_IMPORT_DIR."/tz_log.txt";
        $now = date("Y-m-d H:i:s");
        $cnt = file_put_contents($log_file_name, $now." ".$string."\r\n", FILE_APPEND);
    }
    // Валидация файлов
    public static function validateFiles($options) {
        $result = array();

        $files = $options['files'];
        foreach ($files['tmp_name'] as $key => $tempName) {
            $name = $files['name'][$key];
            $size = filesize($tempName);
            $type = $files['type'][$key];

            // Проверяем размер
            if ($size > $options['maxSize']) {
                array_push($result, array(
                    'name' => $name,
                    'errorCode' => 'big_file'
                ));
            }

            // Проверяем тип файла
            if (!in_array($type, $options['types'])) {
                array_push($result, array(
                    'name' => $name,
                    'errorCode' => 'wrong_type'
                ));
            }
        }

        return $result;
    }
}