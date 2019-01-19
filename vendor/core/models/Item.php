<?php
namespace Dcs\Vendor\Core\Models;

use Dcs\Vendor\Core\Models\Entity;
use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

class Item extends Sheet implements I_Sheet
{
    use T_Sheet;
    use T_Entity;
    use T_Item;
    
    protected $setid;
    
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
        $this->setid = $arData['headid'];
        $this->name = $arData['name']; 
        $this->mdid = $arData['mdid'];
        if ($this->name === '_new_') {
            $this->isnew = true;
        }    
        $this->synonym = $arData['synonym']; 
        $this->mdname = $arData['mdname'];
        $this->mdsynonym = $arData['mdsynonym'];
        $this->mditem = $arData['mditem'];
        $this->mdtypename = $arData['mdtypename'];
        if ($hd) {
            $this->set_head($hd);
        } else {    
            $this->head = $this->get_head();
        }
        if ($this->isnew) {
            foreach ($this->head->getplist() as $prop) {
                if ($prop['valmdtypename'] == 'Items') {
                    $this->mdid = $prop['valmdid'];
                    $this->mdname = $prop['name_valmdid'];
                    $this->mditem = $prop['valmditem'];
                    $this->mdtypename = $prop['valmdtypename'];
                    break;
                }
            }
        }
        $this->plist = array();
        $this->data = array();
        $this->version = time();
        
    }
    public function txtsql_forDetails() 
    {
      return "select et.id, it.rank as name, '' as synonym, it.parentid as headid,
                    et.mdid , md.name as mdname, md.synonym as mdsynonym, 
                    md.mditem, tp.name as mdtypename, tp.synonym as mdtypedescription 
                    FROM \"ETable\" as et
                        INNER JOIN \"MDTable\" as md
                            INNER JOIN \"CTable\" as tp
                            ON md.mditem = tp.id
                        ON et.mdid = md.id 
                    	INNER JOIN \"SetDepList\" as it 
                        ON et.id = it.childid
                    WHERE et.id = :id";  
    }
    public function getprop_classname()
    {
        return 'EProperty';
    }
    public function getplist()
    {
        $sql = $this->txtsql_properties("mdid");
        $properties = array();
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $row;
        }  
        $this->plist = $properties;
        return $properties;
    }        
    public function getArrayNew($newobj)
    {
        return array('id' => $newobj['id'], 
                    'name' => '_new_',
                    'synonym' => 'Новый',
                    'headid' => $newobj['headid'],
                    'mdid' => '',
                    'mdname' => $newobj['classname'],
                    'mdsynonym' => '',
                    'mditem' => '',
                    'mdtypename' => '',
                    'mdtypedescription' => '');
    }        
    public function add_navlist(&$navlist) 
    {
        if ($this->head) {
            $phead = $this->head;
            $phead->add_navlist($navlist); 
            $navlist[] = array('id'=>$this->head->getdocid()."?dcs_propid=".$this->head->getpropid(),'name'=>sprintf("%s",$this->head));
        }
    }
    public function getaccessright_id()
    {
        return $this->get_head()->get_head()->get_mdid();
    }        
    public function get_set_by_item()
    {
        $sql = "SELECT parentid, childid, rank FROM \"SetDepList\" "
                . "where childid = :itemid";
        $res = DataManager::dm_query($sql,array('itemid'=>$this->id));
        if(!$res) {
            return DCS_EMPTY_ENTITY;
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($row)) 
        {
            return DCS_EMPTY_ENTITY;
        }
        return $row['parentid'];
    }
    function head() 
    {
        if ($this->setid) {
            return new Sets($this->setid);
        }    
        return new Sets($this->get_set_by_item());
    }
    public function dbtablename()
    {
        return 'ETable';
    }
    public function item_classname()
    {
        return NULL;
    }        
//    public function getNameFromData($context,$data='') 
//    {
//        if (!count($this->data)) {
//            $this->load_data($context);
//        }
//        $artoStr = array();
//        foreach($this->plist as $prop) {
//            if ($prop['ranktostring'] > 0) {
//              $artoStr[$prop['id']] = $prop['ranktostring'];
//            }
//        }
//        if (!count($artoStr)) {
//            foreach($this->plist as $prop) {
//                if ($prop['rank'] > 0) {
//                  $artoStr[$prop['id']] = $prop['rank'];
//                }  
//            }
//            if (count($artoStr)) {
//              asort($artoStr);
//              array_splice($artoStr,1);
//            }  
//        } else {
//            asort($artoStr);
//        }
//        if (count($artoStr)) {
//            $res = '';
//            foreach($artoStr as $prop => $rank) {
//                $pkey = array_search($prop, array_column($this->plist,'id'));
//                $name = $this->data[$prop]['name'];
//                if ($this->plist[$pkey]['name_type'] == 'date') {
//                    $name = substr($name,0,10);
//                }
//                $res .= ' '.$name;
//            }
//            if ($res != '') {
//                $res = substr($res, 1);
//            }    
//            return $res;
//        } else {
//            return $this->name;
//        }
//    }
//    public function createItem($name,$prefix='')
//    public static function getPropsUse($mditem) 
//    {
//        $sql = "SELECT pu.id, pu.name, pu.synonym, pv_propid.value as propid, 
//                     pv_type.value as type, ct_type.name as name_type, 
//                     pv_len.value as length, pv_prc.value as prec, 
//                     pv_valmd.value as valmdid, md_valmd.name as valmdname 
//                     FROM \"CTable\" as pu 
//                inner join \"CPropValue_cid\" as pv_propid 
//                    inner join \"CProperties\" as cp_propid
//                    ON pv_propid.pid=cp_propid.id
//                    AND cp_propid.name='propid'
//                    inner join \"CTable\" as ct_propid
//                    ON pv_propid.value = ct_propid.id
//                    
//                    inner join \"CPropValue_cid\" as pv_type
//                        inner join \"CProperties\" as cp_type
//                        ON pv_type.pid=cp_type.id
//                        AND cp_type.name='type'
//                        inner join \"CTable\" as ct_type
//                        ON pv_type.value = ct_type.id
//                    ON pv_propid.value = pv_type.id
//                    AND ct_propid.mdid = cp_type.mdid
//                    left join \"CPropValue_int\" as pv_len
//                        inner join \"CProperties\" as cp_len
//                        ON pv_len.pid=cp_len.id
//                        AND cp_len.name='length'
//                    ON pv_propid.value = pv_len.id
//                    AND ct_propid.mdid = cp_len.mdid
//                    
//                    left join \"CPropValue_int\" as pv_prc
//                        inner join \"CProperties\" as cp_prc
//                        ON pv_prc.pid=cp_prc.id
//                        AND cp_prc.name='prec'
//                    ON pv_propid.value = pv_prc.id
//                    AND ct_propid.mdid = cp_prc.mdid
//                    
//                    left join \"CPropValue_mdid\" as pv_valmd
//                        inner join \"CProperties\" as cp_valmd
//                        ON pv_valmd.pid=cp_valmd.id
//                        AND cp_valmd.name='valmdid'
//                        inner join \"MDTable\" as md_valmd
//                        ON pv_valmd.value = md_valmd.id
//                    ON pv_propid.value = pv_valmd.id
//                    AND ct_propid.mdid = cp_valmd.mdid
//                    
//                ON pu.id=pv_propid.id
//                AND pu.mdid = cp_propid.mdid
//                inner join \"CPropValue_cid\" as pv_mditem
//                    inner join \"CProperties\" as cp_mditem
//                    ON pv_mditem.pid=cp_mditem.id
//                    AND cp_mditem.name='mditem'
//                ON pu.id=pv_mditem.id
//                AND pv_mditem.value = :mditem";
//        $params = array();
//        $params['mditem'] = $mditem;
//        $res = DataManager::dm_query($sql,$params); 
//        return $res->fetchAll(PDO::FETCH_ASSOC);
//        
//    }
    public function create_object($name,$synonym='') 
    {
        $objs = array();
        $objs['PSET'] = $this->getProperties(true,'toset');
        $sql = "INSERT INTO \"ETable\" (id, mdid, name) VALUES (:id, :mdid, :name) RETURNING \"id\"";
        $params = array();
        $params['id']=$this->id;
        $params['mdid']=$this->mdid;
        $params['name']= str_replace('Set','Item', $name);
        try {
            $res = DataManager::dm_query($sql,$params); 
            $rank = DataManager::saveItemToSetDepList($this->setid,$this->id);
            if (!$rank)
            {
                throw new DcsException('unable save item to setdeplist');
            }    
            $arPropsUse = DataManager::getPropsUse($this->getmditem());
            $irank=0;
            $plist = $this->getplist();
            foreach ($arPropsUse as $prop)
            {
                $irank++;
                $propid = $prop['propid'];
                $arr_prop = array_filter($plist,function($rw) use ($propid) { 
                                return $rw['propid'] == $propid;
                            });
                if (!count($arr_prop))
                {    
                    $data = array();
                    $data['name'] = $prop['name'];
                    $data['synonym'] = $prop['synonym'];
                    $data['mdid']=$this->mdid;
                    $data['mdtypename']=$this->mdtypename;
                    $data['rank']=$irank;
                    $data['ranktoset']=$irank;
                    $data['ranktostring']=$irank;
                    if (isset($prop['length']))
                    {
                        $data['length'] = $prop['length'];
                    }   
                    if (isset($prop['prec']))
                    {
                        $data['prec'] = $prop['prec'];
                    }   
                    $data['pid'] = $prop['propid'];
                    if ($prop['name_type']=='date')
                    {    
                        $data['isedate']='true';
                    }
                    $row = DataManager::createProperty($data);
                } else {
                    $row = current($arr_prop);
                }
                if (strtolower($prop['name'])==='rank')
                {
                    $sql="INSERT INTO \"IDTable\" (entityid, propid, userid) VALUES (:entityid, :propid, :userid) RETURNING \"id\"";
                    $params = array();
                    $params['entityid']=$this->id;
                    $params['propid']=$row['id'];
                    $params['userid']=$_SESSION["user_id"];
                    try {
                        $res = DataManager::dm_query($sql,$params); 
                    } catch (DcsException $ex) {
                        throw $ex;
                    }    
                    $rowid = $res->fetch(PDO::FETCH_ASSOC);
                    $sql="INSERT INTO \"PropValue_int\" (id, value) VALUES (:id, :value)";
                    $params = array();
                    $params['id']=$rowid['id'];
                    $params['value']=$rank;
                    try {
                        $res = DataManager::dm_query($sql,$params); 
                    } catch (DcsException $ex) {
                        throw $ex;
                    }    
                    $rowid = $res->fetch(PDO::FETCH_ASSOC);
                }    
            }    
        } catch (DcsException $e) {
            throw $e;
        }    
    }        
//    public function get_navlist($context)
//    {
//        $navlist = array();
//        $strkey = 'new';
//        $strval = 'Новый';
//        if ($this->id) {
//            $strkey = $this->id;
//            $strval = sprintf("%s",$this);
//        }    
//        if (isset($context['DATA']['docid'])) {
//            $docid = $context['DATA']['docid']['id'];
//            $doc = new Entity($docid);
//            if (isset($context['DATA']['propid'])) {
//                $propid = $context['DATA']['propid']['id'];
//                $doc->add_navlist($navlist);
//                $navlist[] = array('id'=>$docid,'name'=>sprintf("%s",$doc));
//                $prop = $doc->getProperty($propid);
//                $navlist[] = array('id'=>"$docid?propid=".$propid,'name' => $prop['synonym']);
//                $strkey .= "?docid=$docid&propid=$propid";
//            }
//        }
//        if (!count($navlist)) {    
//            $this->add_navlist($navlist);
//        }    
//        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
//        return $navlist;
//    }        
}