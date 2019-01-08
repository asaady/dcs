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
    
    protected $mdid;
    protected $mditem;
    protected $mdname;
    protected $mdsynonym;
    protected $mdtypename;
    protected $isnew;
    
    //array property value
    //associative array 
    //id is a key
    protected $data;
    //property list for data
    //indexed array
    protected $plist;
    //array of child objects
    //associative array 
    //id is a key
    protected $items;
    //property list for items
    //associative array 
    //id is a key
    protected $properties;
    //property list for item property
    //indexed array
    protected $itemplist;
    
    public function __construct($id,$hd='')
    {
        if ($id === '') {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        $this->id = $id;
        $this->isnew = false;
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
        if ($hd) {
//            if ($hd->getid() !== $this->mdid) {
//                throw new DcsException("Class ".get_called_class().
//                " constructor: head is wrong",DCS_ERROR_WRONG_PARAMETER);
//            }
            $this->set_head($hd);
        } else {    
            $this->head = $this->get_head();
        }
//        if ($this->head) {
//
//            $this->mdname = $this->head->getname();
//            $this->mdsynonym = $this->head->getsynonym();
//            $this->mditem = $this->head->getmditem();
//            $this->mdtypename = $this->head->getmdtypename();
//        }    
        if ($this->name === '_new_') {
            $this->isnew = true;
        }    
        $this->plist = array();
        $this->data = array();
        $this->version = time();
        
    }
   function get_mdid()
    {
        return $this->mdid;
    }
    function getmditem()
    {
        return $this->mditem;
    }
    function get_mdsynonym()
    {
        return $this->mdsynonym;
    }
    public function getmdtypename() 
    {
        return $this->mdtypename;
    }
    function set_head($head)
    {
        $this->head = $head;
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
    public static function txtsql_access() 
    {
        return '';
    }
    public function getitemplist() 
    {
        return array(
            'id',
            'name',
            'synonym',
            'propid',
            'name_type',
            'rank',
            'ranktoset',
            'ranktostring',
            'valmdid',
            'name_valmdid',
            'valmdtypename',
            'field',
            'class'
             );
//        return array(
//            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
//                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>1),
//            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
//                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
//                        'rank'=>3,'ranktoset'=>3,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'TYPE',
//                        'rank'=>0,'ranktoset'=>5,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
//                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
//                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
//                        'rank'=>9,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID',
//                        'rank'=>19,'ranktoset'=>8,'ranktostring'=>0,
//                        'name_type'=>'mdid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID',
//                        'rank'=>0,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>0),
//            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME',
//                        'rank'=>21,'ranktoset'=>9,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD',
//                        'rank'=>25,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>0),
//             );
    }        
}

