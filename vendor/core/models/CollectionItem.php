<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

class CollectionItem extends Sheet implements I_Sheet, I_Item
{
    use T_Sheet;
    use T_Collection;
    use T_Item;
    
    public function dbtablename()
    {
        return 'CTable';
    }
    public function getplist()
    {
        $sql = DataManager::get_select_cproperties("WHERE mp.mdid = :mdid");
        $res = DataManager::dm_query($sql,array('mdid'=>$this->mdid));
        $this->plist = array();
        $this->plist[] = array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'id_type'=>'str', 'name_type'=>'str',
                        'id_valmdid'=>'', 'name_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1);
        $this->plist[] = array('id'=>'synonym','name'=>'synonym','synonym'=>'Синоним',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'id_type'=>'str', 'name_type'=>'str',
                        'id_valmdid'=>'', 'name_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1);
        while ($prop = $res->fetch(PDO::FETCH_ASSOC)) {
            $this->plist[] = $prop;
        }  
        return $this->plist;
    }        
    public function txtsql_forDetails() 
    {
        return "SELECT ct.id, ct.mdid, ct.name, ct.synonym, "
                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                . "FROM \"CTable\" as ct "
                    . "INNER JOIN \"MDTable\" as mc "
                        . "INNER JOIN \"CTable\" as tp "
                        . "ON mc.mditem = tp.id "
                    . "ON ct.mdid = mc.id "
                . "WHERE ct.id=:id";
    }        
    function head() 
    {
        return new CollectionSet($this->mdid);
    }
    function item($id='') {
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
     public function loadProperties()
    {
        return array();
    }        
   public function get_tt_sql_data()
    {
        $sql = "SELECT ct.id, ct.name, ct.synonym, ct.mdid";
        $join = " FROM \"CTable\" AS ct";
        $params = array();
        if (!count($this->plist)) {
            $this->getplist();
        }
        foreach ($this->plist as $row)
        {
            if ($row['field'] == 0) {
                continue;
            }
            if (($row['id'] == 'name')||($row['id'] == 'synonym')) {
                continue;
            }
            $rowname = Filter::rowname($row['id']);
            $rowtype = $row['name_type'];
            if ($rowtype=='cid')
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
            }    
            elseif ($rowtype=='mdid')
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
            }    
            else 
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as name_$rowname, '' as id_$rowname";
                
            }
            $params["pv_$rowname"]=$row['id'];
        }        
        $sql = $sql.$join." WHERE ct.id = :id";
        $params['id'] = $this->id;
        $artemptable = array();
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',$params);   
        return $artemptable;
    }        
    function before_delete() {
        return array($this->id=>array('id'=>$this->id,'name'=>"Элемент коллекции ".$this->head->getsynonym(),'pval'=>$this->synonym,'nval'=>'Удалить'));
    }    
    function delete() {
        $sql = "DELETE FROM \"CTable\" WHERE id=:id";
        $params = array();
        $params['id'] = $this->id;
        $res = DataManager::dm_query($sql,$params);        
        $ares = array('status'=>'OK', 'id'=>$this->head->getid());
        if(!$res) {
            $ares = array('status'=>'ERROR', 'msg'=>$sql);
        }
    }    
    public function before_save($data='') 
    {
        if (!$data) {
            $context = DcsContext::getcontext();
            $data = $context->getattr('DATA');
        }    
        $this->load_data();
        $objs = array();
        foreach($this->plist as $row)
        {    
            $key = $row['name'];
            $id = $row['id'];
            $type = $row['name_type'];
            if ($key=='id') 
            {
                continue;
            }    
            if (array_key_exists($id, $data))
            {
                $dataname = $data[$id]['name'];
                $valname = $this->data[$id]['name'];
                $dataid = $data[$id]['id'];
                $valid = $this->data[$id]['id'];
                if (($type=='id')||($type=='cid')||($type=='mdid')) 
                {
                    if ($dataid==$valid)
                    {
                        continue;
                    }    
                }    
                else
                {
                    if ($dataname==$valname)
                    {
                        continue;
                    }    
                }    
                $objs[]=array('name'=>$key, 'pval'=>$valname, 'nval'=>$dataname);
            }    
        }
 	return $objs;
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
//    function create()
//    {
//        
//        $curname = $data['name']['name'];
//        if ($curname=='') {
//            return array('status'=>'ERROR', 'msg'=>'Name is empty');
//        }    
//        if ($this->head->getname()=='Users') {
//            $user = new User;
//            return $user->create($data);
//        }    
//        $sql ="INSERT INTO \"CTable\" (name, synonym, mdid) "
//                . "VALUES (:name, :synonym, :mdid) RETURNING \"id\"";
//        $params = array('name' => $curname, 
//                'synonym'=>$data['synonym']['name'],
//                'mdid'=> $this->head->getid());
//        $res = DataManager::dm_query($sql,$params);
//        $row = $res ->fetch(PDO::FETCH_ASSOC);
//        $id = $row['id'];
//        $ares = array('status'=>'OK', 'id'=>$id);
//        foreach ($this->plist as $f)
//        {   
//            if (!array_key_exists($f['id'],$data)) continue;
//            $dataname= $data[$f['id']];
//            $type= $f['name_type'];
//            if ($dataname['name']=='')
//            {
//                continue;
//            }
//            $val = $dataname['name'];
//            if ($type == 'bool') {
//                if ($val == 't') {
//                    $val = 'true';
//                }
//                if ($val != 'true') {
//                    $val = 'false';
//                }
//            } elseif (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
//                $val = $dataname['id'];
//            }    
//
//            $sql = "INSERT INTO \"CPropValue_$type\" (id, pid, value) "
//                    . "VALUES (:id, :pid, :value) RETURNING \"id\"";
//            $params = array();
//            $params['id'] = $id;
//            $params['pid'] = $f['id'];
//            $params['value'] = $val;
//            DataManager::dm_query($sql,$params) or 
//                DcsException::doThrow('$sql: '.$sql, DCS_ERROR_SQL);
//        }    
//        return $ares;
//    }
    public function update_dependent_properties($data)
    {
        
    }        
    public function update_properties($data,$n=0)
    {
        $objs = array();
        $objs['status']='OK';
        $objs['objs']=array();
        $objs['id']=$this->id;
        if ($this->head->getname()=='Users')
        {
            $user = new User;
            $ares = $user->update($data);
            return $objs;
        }    
        $this->load_data();
        $id = 'name';
        $sql = '';
        $params = array();
        if (array_key_exists($id, $data))
        {
            $dataname = $data[$id]['name'];
            $valname = $this->data[$id]['name'];
            if ($dataname!=$valname)
            {
                $sql .= ", $id=:$id";
                $params[$id] = $dataname;
            }    
        }    
        $id = 'synonym';
        if (array_key_exists($id, $data))
        {
            $dataname = $data[$id]['name'];
            $valname = $this->data[$id]['name'];
            if ($dataname!=$valname)
            {
                $sql .= ", $id=:$id";
                $params[$id] = $dataname;
            }    
        }    
        if ($sql != '')
        {
            $sql = substr($sql,1);
            $sql = "UPDATE \"CTable\" SET$sql WHERE id=:id";
            $params['id'] = $this->id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
                return $objs;
            }
        }    
        foreach($this->plist as $row)
        {    
            $key = $row['name'];
            $id = $row['id'];
            if (($key=='id')||($key=='name')||($key=='synonym'))
            {
                continue;
            }    
            if (array_key_exists($id, $data))
            {
                $dataname = $data[$id]['name'];
                $valname = $this->data[$id]['name'];
                $dataid = $data[$id]['id'];
                $valid = $this->data[$id]['id'];
                if (($row['name_type']=='id')||($row['name_type']=='cid')||($row['name_type']=='mdid')) 
                {
                    if ($dataid!='')
                    {
                        if ($dataid===$valid)
                        {
                            continue;
                        }    
                        $val = $dataid;
                    }
                    else 
                    {
                        if ($valid!='')
                        {
                            $val = TZ_EMPTY_ENTITY;
                        }
                        else
                        {
                            continue;
                        }    
                    }
                }    
                else
                {
                    if (isset($dataname))
                    {
                        if ($dataname===$valname)
                        {
                            continue;
                        }    
                        if (($dataname=='')&&($valname==''))
                        {
                            continue;
                        }    
                        $val = $dataname;
                    }
                    else
                    {
                        continue;
                    }    
                }    
                $params = array();
                $sql = "SELECT value FROM \"CPropValue_$row[name_type]\" WHERE id=:id and pid=:pid";
                $params['id'] = $this->id;
                $params['pid'] = $id;
                $res = DataManager::dm_query($sql,$params);
                $rw = $res->fetch(PDO::FETCH_ASSOC);
                if ($rw)
                {    
                    $sql = "UPDATE \"CPropValue_$row[name_type]\" SET value=:val, userid=:userid, dateupdate=DEFAULT WHERE id=:id and pid=:pid returning \"id\"";
                }
                else
                {
                    $sql = "INSERT INTO \"CPropValue_$row[name_type]\" (id, pid, value, userid) VALUES (:id, :pid, :val, :userid) returning \"id\"";
                }    
                $params['val'] = $val;
                $params['userid']=$_SESSION["user_id"];
                $res = DataManager::dm_query($sql,$params);
                $rw = $res->fetch(PDO::FETCH_ASSOC);
                if(!$rw) 
                {
                    $objs['status']='ERROR';
                    $objs['msg']=$sql;
                    return $objs;
                }
            }    
        }

	return $objs;        
    }        
    public function get_items() 
    {
        return NULL;
    }        
}
