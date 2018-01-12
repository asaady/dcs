<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class Head extends Model {
    use T_Head;
    
    protected $mditem;     
    
    public function __construct($id='') {
	if ($id=='') {
            throw new Exception("empty head id");
	}
        $this->id = $id; 
        $this->version = time();
        $this->getMD();
    }
    function getmditem()
    {
        return $this->mditem;
    }
    public function getMD() 
    {
	$sql = "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, "
                . "mdi.name as mdtypename, mdi.synonym as mdtypedescription "
                . "FROM \"MDTable\" AS mdt "
                . "INNER JOIN \"CTable\" AS mdi "
                . "ON mdt.mditem=mdi.id "
                . "WHERE mdt.id= :mdid";
        $sth = DataManager::dm_query($sql,array('mdid'=>$this->id));        
	$arPar = $sth->fetch(PDO::FETCH_ASSOC);
        $this->name = $arPar['name']; 
        $this->synonym = $arPar['synonym']; 
        $this->mditem = new Mditem($arPar['mditem']); 
    }
    public static function getPropsUse() 
    {
        $sql="SELECT pu.id, pu.name, pu.synonym, pv_propid.value as propid, 
                pv_type.value as type, ct_type.name as name_type, 
                pv_len.value as length, pv_prc.value as prec, 
                pv_valmd.value as valmdid, md_valmd.name as valmdname 
                FROM \"CTable\" as pu 
                inner join \"CPropValue_cid\" as pv_propid 
                    inner join \"CProperties\" as cp_propid
                    ON pv_propid.pid=cp_propid.id
                    AND cp_propid.name='propid'
                    inner join \"CTable\" as ct_propid
                    ON pv_propid.value = ct_propid.id
                    
                    inner join \"CPropValue_cid\" as pv_type
                        inner join \"CProperties\" as cp_type
                        ON pv_type.pid=cp_type.id
                        AND cp_type.name='type'
                        inner join \"CTable\" as ct_type
                        ON pv_type.value = ct_type.id
                    ON pv_propid.value = pv_type.id
                    AND ct_propid.mdid = cp_type.mdid

                    left join \"CPropValue_int\" as pv_len
                        inner join \"CProperties\" as cp_len
                        ON pv_len.pid=cp_len.id
                        AND cp_len.name='length'
                    ON pv_propid.value = pv_len.id
                    AND ct_propid.mdid = cp_len.mdid
                    
                    left join \"CPropValue_int\" as pv_prc
                        inner join \"CProperties\" as cp_prc
                        ON pv_prc.pid=cp_prc.id
                        AND cp_prc.name='prec'
                    ON pv_propid.value = pv_prc.id
                    AND ct_propid.mdid = cp_prc.mdid
                    
                    left join \"CPropValue_mdid\" as pv_valmd
                        inner join \"CProperties\" as cp_valmd
                        ON pv_valmd.pid=cp_valmd.id
                        AND cp_valmd.name='valmdid'
                        inner join \"MDTable\" as md_valmd
                        ON pv_valmd.value = md_valmd.id
                    ON pv_propid.value = pv_valmd.id
                    AND ct_propid.mdid = cp_valmd.mdid
                    
                ON pu.id=pv_propid.id
                AND pu.mdid = cp_propid.mdid
                inner join \"CPropValue_cid\" as pv_mditem
                    inner join \"CProperties\" as cp_mditem
                    ON pv_mditem.pid=cp_mditem.id
                    AND cp_mditem.name='mditem'
                ON pu.id=pv_mditem.id
                AND pv_mditem.value = :mditem";
        $params = array();
        $params['mditem']=$this->mditem->getid();
        $res = DataManager::dm_query($sql,$params); 
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
}

