<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

abstract class Sheet extends Model implements I_Sheet
{
    use T_Sheet;
    
    //сущность верхнего уровня
    //для строк это табличная часть
    //для табличной части - документа(справочник)
    //для сущности - набор сущностей 
    //для набора сущностей - класс сущностей
    //для реквизита метаданные сущности
    protected $head;     
    protected $data;
    protected $mdid;
    protected $mditem;
    protected $mdname;
    protected $mdsynonym;
    protected $mdtypename;
    
    public function __construct($id)
    {
        if ($id === '') {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        $this->id = $id;
        $arData = $this->getDetails($id);
        if (!$arData) {
            throw new DcsException("Class ".get_called_class().
                " constructor: id is wrong",DCS_ERROR_WRONG_PARAMETER);
        }
        $this->mdid = $arData['mdid'];
        $this->name = $arData['name']; 
        $this->synonym = $arData['synonym']; 
        $this->mdname = $arData['mdname'];
        $this->mdsynonym = $arData['mdsynonym'];
        $this->mditem = $arData['mditem'];
        $this->mdtypename = $arData['mdtypename'];
        $this->head = $this->get_head();
        if ($this->name === '_new_') {
            $arData = $this->head->getDetails($this->mdid);
            $this->mdname = $arData['name'];
            $this->mdsynonym = $arData['synonym'];
            $this->mditem = $arData['mditem'];
            $this->mdtypename = $arData['mdtypename'];
        }    
        $this->properties = $this->loadProperties();
        $this->data = array();
        $this->version = time();
        
    }
    function get_mdid()
    {
        return $this->mdid;
    }
    function set_head($head)
    {
        $this->head = $head;
    }
    function __toString() 
    {
      return $this->synonym;
    }
    function getdata() 
    {
        return $this->data;
    }
    function set_data($data) 
    {
	foreach($this->properties as $aritem)
        {
	    $v = $aritem['id'];
            $this->data[$v]=array();
	    if(array_key_exists($v,$data))
            {
                $this->data[$v]['name']=$data[$v]['name'];
                if (($aritem['type'] === 'id')||
                    ($aritem['type'] === 'cid')||
                    ($aritem['type'] === 'mdid')) {
                    if ($data[$v]['id'] !== '')
                    {    
                        $this->data[$v]['id'] = $data[$v]['id'];
                    } else {
                        $this->data[$v]['name'] = '';
                        $this->data[$v]['id'] = DCS_EMPTY_ENTITY;
                    }
                }
	    } else {
                $this->data[$v]['name'] = '';
                $this->data[$v]['id'] = DCS_EMPTY_ENTITY;
	    }  
	}
    }
    public static function getPropsUse($mditem) 
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
        $params['mditem']=$mditem;
        $res = DataManager::dm_query($sql,$params); 
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    public function rowname($arr) {
        $param = str_replace("-","", strtolower($arr['name']));
        return str_replace(" ","", $param);
    }
    public function getmdtypename() 
    {
        return $this->mdtypename;
    }
    public static function txtsql_access() 
    {
        return '';
    }
}

