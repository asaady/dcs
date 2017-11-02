<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';

use tzVendor\Common_data;
use tzVendor\UploadSet;

// Валидация файлов
function validateFiles($options) {
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


// Начало работы скрипта

$csv = $_FILES['csv'];

$destPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING). TZ_UPLOAD_IMPORT_DIR;
UploadSet::tz_log("--------------------------------------\r\n".'start import to: '.$destPath);

// Валидация
$validationErrors = validateFiles(array(
    'files' => $csv,
    'maxSize' => 2 * 1024 * 1024,
    'types' => array('text/csv', 'text/txt')
));

if (count($validationErrors) > 0) {
    // Возвращаем список ошибок клиенту
    echo json_encode($validationErrors);
    exit;
}

$res=array();
// Копирование файлов в нужную папку

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$propid = filter_input(INPUT_POST, 'propid', FILTER_SANITIZE_SPECIAL_CHARS);
UploadSet::tz_log('import in id: '.$id." propid: ".$propid);
if (Common_data::check_uuid($id))
{    
    UploadSet::tz_log('OK validate id=: '.$id);
    try 
    {
        $ent = new \tzVendor\Entity($id);
    } catch (Exception $ex) {
        $res[]=array('code'=>'error','destName'=>$ex->getMessage());
        $ent = FALSE;
    }
    if ($ent!==FALSE)
    {   
        if (Common_data::check_uuid($propid))
        {    
            try 
            {
                $prop = new \tzVendor\Mdproperty($propid);
            } catch (Exception $ex) {
                $res[]=array('code'=>'error','destName'=>$ex->getMessage());
                $prop = FALSE;
            }
            if($prop!==FALSE)
            {    
                $curm = date("Ym");
                UploadSet::tz_log('import to: '.$destPath ."/". $curm);
                if (!file_exists($destPath ."/". $curm))
                {
                    mkdir($destPath ."/". $curm,0777);
                    UploadSet::tz_log('created folder : '.$destPath ."/". $curm);
                }        
                foreach ($csv['name'] as $key => $name) 
                {
                    $tempName = $csv['tmp_name'][$key];
                    $ext = strrchr($name,'.');
                    $destName = $destPath ."/". $curm."/".$id."_".$propid.$ext;
                    UploadSet::tz_log('import to file :'.$destName.' : tempName = '.$tempName);
                    if (move_uploaded_file($tempName, $destName)!==FALSE)
                    {
                        $res[]=array('code'=>'success','destName'=>$destName);
                        UploadSet::tz_log('success import : '.$destName);
                    }
                    else 
                    {
                        $res[]=array('code'=>'error','destName'=>$destName);
                        UploadSet::tz_log('error import : '.$destName);
                    }
                }
            }    
        }
        else
        {
            $res[]=array('code'=>'error','destName'=>'invalid_current_propid'.$propid);
        }    
    }    
}
else
{
    $res[]=array('code'=>'error','destName'=>'invalid_current_entityid='.$id);
}    
    
// Возвращаем ответ клиенту
echo json_encode($res);