<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class EProperty extends Sheet  
{
    use T_Sheet;
    use T_Item;
    use T_Property;
    
    public function txtsql_forDetails()
    {
        return "SELECT mp.id, mp.name, mp.synonym, mp.mdid, 
            md.name as mdname, md.synonym as mdsynonym, 
            md.mditem, tp.name as mdtypename, tp.synonym as mdtypedescription
            FROM \"MDProperties\" AS mp
                INNER JOIN \"MDTable\" as md
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                ON mp.mdid = md.id 
            WHERE mp.id = :id";
    }        
    public function get_tt_sql_data()
    {        
        return "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, mp.synonym, 
            pst.value as type, pt.name as name_type, mp.length, mp.prec, mp.mdid, 
            mp.rank, mp.ranktostring, mp.ranktoset, mp.isedate, mp.isenumber, 
            mp.isdepend, pmd.value as valmdid, valmd.name AS name_valmdid, 
            valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
            mi.name as valmdtypename, 1 as field,'active' as class FROM \"MDProperties\" AS mp
		  LEFT JOIN \"CTable\" as pr
		    LEFT JOIN \"CPropValue_mdid\" as pmd
        		INNER JOIN \"MDTable\" as valmd
                            INNER JOIN \"CTable\" as mi
                            ON valmd.mditem = mi.id
                        ON pmd.value = valmd.id
		    ON pr.id = pmd.id
		    LEFT JOIN \"CPropValue_cid\" as pst
                        INNER JOIN \"CProperties\" as cprs
                        ON pst.pid = cprs.id
                        AND cprs.name='type'
                        INNER JOIN \"CTable\" as pt
                        ON pst.value = pt.id
		    ON pr.id = pst.id
		  ON mp.propid = pr.id
		WHERE mp.id = :id";
    }    
    public function load_data($data='')
    {
        $this->data['id'] = array('id'=>'','name'=>$this->id);
        $this->data['name'] = array('id'=>'','name'=>$this->name);
        $this->data['synonym'] = array('id'=>'','name'=>$this->synonym);
        if (!count($this->plist)) {
            $this->getplist();
        }
        if (!$data) {
            $sql = $this->get_tt_sql_data();
            $sth = DataManager::dm_query($sql,array('id'=>$this->id));        
            while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                foreach($this->plist as $prow) {
                    if (array_key_exists($prow['id'], $row)) {
                        if (($prow['name_type'] == 'cid') || 
                            ($prow['name_type'] == 'id') || 
                            ($prow['name_type'] == 'mdid')) {
                            $this->data[$prow['id']] = array('id'=>$row[$prow['id']],'name'=>$row['name_'.$prow['id']]);
                        } else {
                            $this->data[$prow['id']] = array('id'=>'','name'=>$row[$prow['id']]);
                        }
                    } else {
                        $this->data[$prow['id']] = array('id'=>'','name'=>'');
                    }
                }    
            }
        } else {
            $this->data['id'] = array('id'=>'','name'=>$data['id']);
            foreach($this->plist as $prow) {
                if (array_key_exists("name_".$prow['id'], $data)) {
                    $this->data[$prow['id']] = array(
                        'id'=>$data[$prow['id']],
                        'name'=>$data["name_".$prow['id']]);
                } elseif (array_key_exists($prow['id'], $data)) {
                    $this->data[$prow['id']] = array('id'=>'','name'=>$data[$prow['id']]);
                } else {
                    $this->data[$prow['id']] = array('id'=>'','name'=>'');
                }
            }    
        }    
        $this->version = time();
        $this->head = $this->get_head();
        $this->check_right();
        return $this->data;
    }            
    public static function txtsql_access()
    {
        return '';
    }        
    public function getplist()
    {
        $plist = array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '3'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID',
                        'rank'=>3,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'cid','name_valmdid'=>DCS_PROPS_TEMPL_NAME,'valmdid'=>DCS_PROPS_TEMPL_ID,'valmdtypename'=>'Cols','class'=>'active','field'=>1),
            '4'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE',
                        'rank'=>4,'ranktoset'=>5,'ranktostring'=>0,
                        'name_type'=>'cid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            '5'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '6'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '7'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
                        'rank'=>10,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '8' => array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE',
                        'rank'=>11,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '9' => array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER',
                        'rank'=>12,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '10' => array('id'=>'isdepend','name'=>'isdepend','synonym'=>'ISDEPEND',
                        'rank'=>12,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '11' => array('id'=>'valmdid','name'=>'valmdid','synonym'=>'VALMDID',
                        'rank'=>19,'ranktoset'=>8,'ranktostring'=>0,
                        'name_type'=>'mdid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            '12' => array('id'=>'valmdtypename','name'=>'valmdtypename','synonym'=>'VALMDTYPENAME',
                        'rank'=>20,'ranktoset'=>9,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
             );
        $this->plist = $plist;
        return $plist;
    }        
    public function loadProperties()
    {
        return array();
    }        
//    public static function txtsql_forDetails() 
//    {
//        return '';
////        return "SELECT mp.id, mp.mdid, mp.name, mp.synonym, mp.propid, "
////                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
////                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
////                . "FROM \"MDProperties\" as mp "
////                    . "INNER JOIN \"MDTable\" as mc "
////                        . "INNER JOIN \"CTable\" as tp "
////                        . "ON mc.mditem = tp.id "
////                    . "ON mp.mdid = mc.id "
////                . "WHERE mp.id=:id";
//    }        
    public function head() 
    {
        return new Mdentity($this->mdid);
    }
    public function item($id='')
    {
        return NULL;
    }        
    public function getprop_classname()
    {
        return NULL;
    }        
    public function item_classname()
    {
        return NULL;
    }        
    public function create_object($name,$synonym='')
    {
        return NULL;
    }        
    public function getArrayNew($newobj)
    {
        return array('id' => $newobj['id'], 
                    'name' => '_new_',
                    'synonym' => 'Новый',
                    'mdid' => $newobj['headid'],
                    'mdname' => $newobj['classname'],
                    'mdsynonym' => '',
                    'mditem' => '',
                    'mdtypename' => '',
                    'mdtypedescription' => '');
    }        
    public function getNameFromData($data='')
    {
        if (!$data) {
            return array('name' => $this->name, 'synonym' => $this->synonym);
        } else {
            return array('name' => $data['name']['name'],
                         'synonym' => $data['synonym']['name']);
        }    
    }        
    function get_history_data($entityid)
    {
        if (!count($this->data)) {
            $this->load_data();
        }
        $context = DcsContext::getcontext();
        $propid = $this->id;
        $type = $this->data['type']['name'];
        $clsid='hidden';
        if ($context->getattr('MODE') == 'CONFIG') {
            $clsid='active';
        }    
        $plist = array(
              'id'=>array('name'=>'id','synonym'=>'ID','class'=>$clsid),
              'username'=>array('name'=>'username','synonym'=>'Пользователь','class'=>'active'),
              'dateupdate'=>array('name'=>'dateupdate','synonym'=>'Дата изменения','class'=>'active'),
              'value'=>array('name'=>'value','synonym'=>'Значение','class'=>'active')
            );
        if ($type=='id')
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, pv.value as value, pv.value as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_id\" as pv INNER JOIN \"ETable\" as et ON pv.value = et.id ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id  WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }  
        elseif ($type=='cid')
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, et.synonym as value, pv.value as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_cid\" as pv INNER JOIN \"CTable\" as et ON pv.value = et.id ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }
        else
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, pv.value as value, '' as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_$type\" as pv ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }    
        $res = DataManager::dm_query($sql,array('propid'=>$propid,'entityid'=>$entityid));
        if(!$res) {
            return array('status'=>'ERROR', 'msg'=>$sql);
        }
        $ardata = array();
        $arr_e=array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $ardata[$row['id']]=array();
            foreach($plist as $prop) 
            {
                $ardata[$row['id']][$prop['name']]=array();
                $ardata[$row['id']][$prop['name']]['id'] = '';
                $val = $row[$prop['name']];
                if ($prop['name']=='dateupdate')
                {
                    $dt = new \DateTime($row[$prop['name']]);
                    $val = $dt->format("Y-m-d h:i:s");
                }
                elseif ($prop['name']=='value') 
                {
                    if ($type=='id')
                    {
                        $ardata[$row['id']][$prop['name']]['id'] = $val;
                        if (!in_array($row['value'], $arr_e))
                        {        
                            $arr_e[]= $row['value'];
                        }    
                    }    
                }
                $ardata[$row['id']][$prop['name']]['name'] = $val;
            }
        }
        if (count($arr_e))
        {
            $entset = new EntitySet($this->mdid);
            $arr_entities = $entset->getAllEntitiesToStr($arr_e);
            foreach($ardata as $id=>$row) 
            {
                if (array_key_exists($row['value']['id'], $arr_entities))
                {
                    $ardata[$id]['value']['name'] =$arr_entities[$row['value']['id']]['name'];
                }
            }
        }
        
        return array('LDATA'=>$ardata,'PSET'=>$plist, 'name'=>$this->name,'synonym'=>$this->synonym);
    } 
    function update($data) {
        $sql = '';
        $objs = $this->before_save();
        $params = array();
        foreach($objs as $row)
        {    
            $val = $row['nval'];
            if ($row['name']=='propid')
            {
                $val = $row['nvalid'];
            }   
            $sql .= ", $row[name]=:$row[name]";
            $params[$row['name']] = $val;
        }
        $objs['status']='NONE';
        if ($sql!=''){
            $objs['status']='OK';
            $sql = substr($sql,1);
            $id = $this->id;
            $sql = "UPDATE \"MDProperties\" SET$sql WHERE id=:id";
            $params['id']=$id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                return array('status'=>'ERROR', 'msg'=>$sql);
            }
        }
        return array('status'=>'OK', 'id'=>$this->id);
    }
}
