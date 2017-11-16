<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require '../vendor/autoload.php';

use tzVendor\Entity;
use tzVendor\Common_data;


// Начало работы скрипта

$photos = $_FILES['photos'];

$destPath = $_SERVER['DOCUMENT_ROOT'] . TZ_UPLOAD_DIR;

// Валидация
$validationErrors = Common_data::validateFiles(array(
    'files' => $photos,
    'maxSize' => 2 * 1024 * 1024,
    'types' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif')
));

if (count($validationErrors) > 0) {
    // Возвращаем список ошибок клиенту
    echo json_encode($validationErrors);
    exit;
}

$res=array();
// Копирование файлов в нужную папку

$curid = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$pos_ = strpos($curid,"_");
if ($pos_===FALSE)
{
    $id = $curid;
    $propid = '';
}    
else
{
    $id = substr($curid,0,$pos_);
    $propid = substr($curid,$pos_+1);
}    
if (Common_data::check_uuid($id))
{    
    try 
    {
        $ent = new \tzVendor\Entity($id);
    } catch (Exception $ex) {
        $res[]=array('code'=>'error','destName'=>$ex->getMessage());
        $ent = FALSE;
    }
    if ($ent!==FALSE)
    {   
        if ($propid!=='')
        {   
            if (!Common_data::check_uuid($propid))
            {    
                $res[]=array('code'=>'error','destName'=>'invalid_current_propid='.$propid);
            }
            else 
            {
                $curm = date("Ym");
                if (!file_exists($destPath . $curm))
                {
                    mkdir($destPath . $curm,0777);
                }        
                foreach ($photos['name'] as $key => $name) 
                {
                    $tempName = $photos['tmp_name'][$key];
                    $ext = strrchr($name,'.');
                    $destName = $destPath ."/". $curm."/".$curid.$ext;
                    if (move_uploaded_file($tempName, $destName)!==FALSE)
                    {
                        $res[]=array('code'=>'success','destName'=>$destName);
                    }
                    else 
                    {
                        $res[]=array('code'=>'error','destName'=>$destName);
                    }
                }
            }
        }
        else 
        {
            $res[]=array('code'=>'error','destName'=>'invalid_current_propid='.$propid);
        }
    }    
}
else
{
    $res[]=array('code'=>'error','destName'=>'invalid_current_entityid='.$id);
}    
    
// Возвращаем ответ клиенту
echo json_encode($res);