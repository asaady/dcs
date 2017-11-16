<?php
namespace tzVendor;
use PDO;
use DateTime;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class Download {
    protected $id;
    public function __construct($id) 
    {
        $this->id = $id;
    }
    
    public function get_data($propid) 
    {
        $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, pv.value as value, '' as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_file\" as pv ON it.id=pv.id 
                LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        $res = DataManager::dm_query($sql,array('propid'=>$propid,'entityid'=>$this->id));
        if(!$res) {
            header('HTTP/1.1 404 Not Found');
            header("Error: 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $destPath = $_SERVER['DOCUMENT_ROOT'] . TZ_UPLOAD_DIR;
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $fname = $row['value'];
            if ($fname=='')
            {
                header('HTTP/1.1 404 Not Found');
                header("Error: 404 Not Found");
                header("Status: 404 Not Found");
                exit;
            } 
            $curm = date("Ym",strtotime($row['dateupdate']));
            $fname = $row['value'];
            $ext = strrchr($fname,'.');
            $file = $destPath."/".$curm."/".$this->id."_".$propid.$ext;
            if (file_exists($file)) 
            {
                $fsize=filesize($file);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$fname.'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . $fsize);
                readfile($file);
                exit;
            }
        }    
    }    
}