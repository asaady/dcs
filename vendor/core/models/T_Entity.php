<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\Filter;

trait T_Entity {
    public function txtsql_property($parname)
    {
        return DataManager::get_select_properties(" WHERE mp.id = :$parname ");    
    }        
    public function txtsql_properties($parname)
    {
        return DataManager::get_select_properties(" WHERE mp.mdid = :$parname ");    
    }        
    public function txtsql_forDetails() 
    {
        return "select et.id, '' as name, '' as synonym, 
                    et.mdid , md.name as mdname, md.synonym as mdsynonym, 
                    md.mditem, tp.name as mdtypename, tp.synonym as mdtypedescription 
                    FROM \"ETable\" as et
                        INNER JOIN \"MDTable\" as md
                            INNER JOIN \"CTable\" as tp
                            ON md.mditem = tp.id
                        ON et.mdid = md.id 
                    WHERE et.id = :id";  
    }
    //ret: array temp table names 
    public function get_tt_sql_data()
    {
        $artemptable = array();
        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid "
                . "FROM \"IDTable\" as it "
                . "INNER JOIN \"MDProperties\" as mp "
                . "ON it.propid = mp.id AND mp.mdid = :mdid "
                . "WHERE it.entityid = :id "
                . "GROUP BY it.entityid, it.propid";
        $artemptable[] = DataManager::createtemptable($sql,'tt_id',
                array('mdid'=>$this->mdid,'id'=>$this->id));   
        $sql = "SELECT t.id as tid, t.userid, 
                ts.dateupdate, ts.entityid, ts.propid
		FROM \"IDTable\" AS t 
		INNER JOIN tt_id AS ts
                ON t.entityid=ts.entityid
		AND t.propid = ts.propid
		AND t.dateupdate=ts.dateupdate";
        $artemptable[] = DataManager::createtemptable($sql,'tt_tv');   
        $str0_req='SELECT et.id';
        $str_req='';
        $str_p = '';
        foreach($this->plist as $row) 
        {
            if ($row['id'] == 'id') {
                continue;
            }
            if ($row['field'] == 0) {
                continue;
            }
            $rid = $row['id'];
            $rowname = Filter::rowname($rid);
            $rowtype = $row['name_type'];
            $str0_t = ", tv_$rowname.propid as propid_$rowname, pv_$rowname.value as name_$rowname, '' as id_$rowname";
            $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            if ($rowtype=='id') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, '' as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='cid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='mdid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='date') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, to_char(pv_$rowname.value,'DD.MM.YYYY') as name_$rowname, '' as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            $str0_req .= $str0_t;
            $str_req .=$str_t;
        }
        $str0_req .=" FROM \"ETable\" as et";
        $sql = $str0_req.$str_req." WHERE et.id=:id";
        //die($sql.' id ='.$this->id);
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
    }    
    public function get_valid($propid)
    {
        $sql = "SELECT it.entityid, it.propid, pv.value from \"IDTable\" as it "
                . "inner join (SELECT it.entityid, it.propid, "
                . "max(it.dateupdate) as dateupdate "
                . "from \"IDTable\" as it "
                . "where it.entityid = :id and it.propid = :propid "
                . "group by it.entityid, it.propid) as slc "
                . "on it.entityid = slc.entityid "
                . "and it.propid = slc.propid "
                . "and it.dateupdate = slc.dateupdate "
                . "inner join \"PropValue_id\" as pv on it.id=pv.id";
        $res = DataManager::dm_query($sql,array('id'=>$this->id, 'propid'=>$propid));
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            return $row['value'];
        }  
        return '';
    }
    public function getNameFromData($data='')
    {
        if (!count($this->plist)) {
            $this->plist = $this->getplist();
        }
        if (!$data) {
            if (!count($this->data)) {
                $this->load_data();
            }
            $data = $this->data;
        }
        $artoStr = array();
        $isDocs = $this->mdtypename === 'Docs';
        foreach($this->plist as $prop)
        {
            if (!array_key_exists($prop['id'],$data)) {
                continue;
            }
            if ($prop['ranktostring'] > 0) 
            {
              $artoStr[$prop['id']] = $prop['ranktostring'];
            }
        }
        if (!count($artoStr)) {
            return array('name' => '',
                     'synonym' => '');
        }    
        asort($artoStr);
        $res = '';
        foreach($artoStr as $pr => $rank) {
            $pkey = array_search($pr, array_column($this->plist,'id'));
            if ($isDocs && ($this->plist[$pkey]['isenumber'] ||
                            $this->plist[$pkey]['isedate'])) {
                continue;
            }    
            if (!array_key_exists($pr,$data)) {
                continue;
            }
            $name = $data[$pr]['name'];
            if ($this->plist[$pkey]['name_type'] == 'date') {
                $name = substr($name,0,10);
            }
            $res .= ' '.$name;
        }
        if ($isDocs) {
            $datetime = new DateTime($this->edate);
            $res = $this->head->getsynonym()." №".$this->enumber." от ".$datetime->format('d-m-y').$res;
        } elseif ($res != '') {
                $res = substr($res, 1);
        }    
        return array('name' => $res,
                     'synonym' => $res);
    }
//    public function getItemsProp($context) 
//    {
//        $plist = array(
//            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
//                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>1),
//            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
//                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
//                        'rank'=>3,'ranktoset'=>3,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID',
//                        'rank'=>2,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'cid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'name_propid'=>array('id'=>'name_propid','name'=>'name_propid','synonym'=>'NAME_PROPID',
//                        'rank'=>0,'ranktoset'=>4,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>0),
//            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE',
//                        'rank'=>3,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'cid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'TYPE',
//                        'rank'=>0,'ranktoset'=>5,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH',
//                        'rank'=>5,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC',
//                        'rank'=>6,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
//                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
//                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
//                        'rank'=>9,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'isedate'=>array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE',
//                        'rank'=>13,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'isenumber'=>array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER',
//                        'rank'=>14,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'isdepend'=>array('id'=>'isdepend','name'=>'isdepend','synonym'=>'IS DEPENDENT',
//                        'rank'=>15,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'bool','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
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
//        if ($context['PREFIX'] == 'CONFIG') {
//            $plist['id']['class'] = 'readonly';
//        }
//        return $plist;
//    }
}            

