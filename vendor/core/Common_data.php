<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

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
    public static function _log($div,$string)
    {
        $log_file_name = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$div."/tz_log.txt";
        $now = date("Y-m-d H:i:s");
        $cnt = file_put_contents($log_file_name, "FROM: ". self::getRealIpAddr()." : ".$now." : ".$string."\r\n", FILE_APPEND);
    }
    public static function import_log($string)
    {
        self::_log(TZ_UPLOAD_IMPORT_LOG, $string);
    }
    public static function upload_log($string)
    {
        self::_log(TZ_UPLOAD_LOG, $string);
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
    public static function toXml($data, $rootNodeName = 'data', $xml=null)
    {
        // включить режим совместимости, не совсем понял зачем это но лучше делать
        if (ini_get('zend.ze1_compatibility_mode') == 1)
        {
          ini_set ('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null)
        {
          $xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName />");
        }

        //цикл перебора массива
        foreach($data as $key => $value)
        {
          // нельзя применять числовое название полей в XML
          if (is_numeric($key))
          {
            // поэтому делаем их строковыми
            $key = "item";
          }

          // удаляем не латинские символы
          $key = preg_replace('/[^a-z0-9_]/i', '', $key);

          // если значение массива также является массивом то вызываем себя рекурсивно
          if (is_array($value))
          {
            $node = $xml->addChild($key);
            // рекурсивный вызов
            self::toXml($value, $rootNodeName, $node);
          }
          else
          {
            // добавляем один узел
            $value = htmlentities($value);
            $xml->addChild($key,$value);
          }

        }
        // возвратим обратно в виде строки  или просто XML-объект
        return $xml->asXML();
    }
    function getRealIpAddr() 
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))        // Определяем IP
        { 
            $ip=$_SERVER['HTTP_CLIENT_IP']; 
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))    // Если IP идёт через прокси
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR']; 
        }
        else 
        { 
            $ip=$_SERVER['REMOTE_ADDR']; 
        }
        return $ip;
    }
}