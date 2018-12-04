<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class CollectionItem extends Sheet implements I_Sheet, I_Property, I_Item
{
    use T_Sheet;
    use T_Collection;
    use T_Item;
    use T_Property;
    use T_CProperty;
    
    public static function txtsql_forDetails() 
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
    function item() {
        return NULL;
    }
    public function get_tt_sql_data()
    {
        $sql = "SELECT ct.id, ct.name, ct.synonym, ct.mdid";
        $join = " FROM \"CTable\" AS ct";
        $params = array();
        foreach ($this->properties as $row)
        {
            if ($row['field'] == 0) {
                continue;
            }
            $rowname = $this->rowname($row);
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
    function update($data) 
    {
        if ($this->head->getname()=='Users')
        {
            $user = new User;
            $ares = $user->update($data);
            $objs['status']='OK';
            $objs['id']=$this->id;
            return $objs;
        }    
        $pdata = $this->getData();
        $objs = array();
        $objs['id'] = $this->id;
        foreach($pdata['SDATA'] as $prow)
        {    
            $id = 'name';
            $sql = '';
            $params = array();
            if (array_key_exists($id, $data))
            {
                $dataname = $data[$id]['name'];
                $valname = $prow[$id]['name'];
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
                $valname = $prow[$id]['name'];
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
                DataManager::dm_query($sql,$params) or 
                        DcsException::doThrow('$sql: '.$sql, DCS_ERROR_SQL);
            }    
            
            foreach($pdata['PLIST'] as $row)
            {
                $key = $row['name'];
                $id = $row['id'];
                $type = $row['name_type'];
                if (($key=='id')||($key=='name')||($key=='synonym')) {
                    continue;
                }    
                if (array_key_exists($id, $data)) {

                    $dataname = $data[$id]['name'];
                    $valname = $prow[$id]['name'];
                    $dataid = $data[$id]['id'];
                    $valid = $prow[$id]['id'];
                    if (($type=='id')||($type=='cid')||($type=='mdid')) 
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
                    $sql = "SELECT value FROM \"CPropValue_$type\" WHERE id=:id and pid=:pid";
                    $params['id'] = $this->id;
                    $params['pid'] = $id;
                    $res = DataManager::dm_query($sql,$params);
                    $rw = $res->fetch(PDO::FETCH_ASSOC);
                    if ($rw) {    
                        $sql = "UPDATE \"CPropValue_$type\" SET value=:val, userid=:userid, dateupdate=DEFAULT WHERE id=:id and pid=:pid returning \"id\"";
                    } else {
                        $sql = "INSERT INTO \"CPropValue_$type\" (id, pid, value, userid) VALUES (:id, :pid, :val, :userid) returning \"id\"";
                    }    
                    $params['val'] = $val;
                    $params['userid']=$_SESSION["user_id"];
                    
                    $res = DataManager::dm_query($sql,$params) or 
                        DcsException::doThrow('$sql: '.$sql, DCS_ERROR_SQL);
                    $res->fetch(PDO::FETCH_ASSOC) or 
                        DcsException::doThrow('$sql: '.$sql, DCS_ERROR_SQL);
                    $objs['status']='OK';
                    $objs['id']=$this->id;
                }    
            }    
        }
	return $objs;
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
    public function before_save($context,$data) 
    {
        $pdata = $this->getData();
        $objs = array();
        foreach($pdata['SDATA'] as $prow)
        {    
            foreach($pdata['PLIST'] as $row)
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
                    $valname = $prow[$id]['name'];
                    $dataid = $data[$id]['id'];
                    $valid = $prow[$id]['id'];
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
        }
 	return $objs;
    }
    function create($data)
    {
        
        $curname = $data['name']['name'];
        if ($curname=='')
        {
            $ares = array('status'=>'ERROR', 'msg'=>'Name is empty');
        }    
        else
        {
            if ($this->head->getname()=='Users')
            {
                $user = new User;
                $ares = $user->create($data);
            }    
            else 
            {
                $sql ="INSERT INTO \"CTable\" (name, synonym, mdid) "
                        . "VALUES (:name, :synonym, :mdid) RETURNING \"id\"";
                $params = array('name' => $curname, 
                        'synonym'=>$data['synonym']['name'],
                        'mdid'=> $this->head->getid());
                $res = DataManager::dm_query($sql,$params);
                $row = $res ->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $ares = array('status'=>'OK', 'id'=>$id);
                $plist = $this->properties;
                foreach ($plist as $f)
                {   
                    if (!array_key_exists($f['id'],$data)) continue;
                    $dataname= $data[$f['id']];
                    $type= $f['name_type'];
                    if ($dataname['name']=='')
                    {
                        continue;
                    }
                    $val = $dataname['name'];
                    if ($type == 'bool') {
                        if ($val == 't') {
                            $val = 'true';
                        }
                        if ($val != 'true') {
                            $val = 'false';
                        }
                    } elseif (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
                        $val = $dataname['id'];
                    }    

                    $sql = "INSERT INTO \"CPropValue_$type\" (id, pid, value) "
                            . "VALUES (:id, :pid, :value) RETURNING \"id\"";
                    $params = array();
                    $params['id'] = $id;
                    $params['pid'] = $f['id'];
                    $params['value'] = $val;
                    DataManager::dm_query($sql,$params) or 
                        DcsException::doThrow('$sql: '.$sql, DCS_ERROR_SQL);
                }    
            }
        }    
        return $ares;
    }
    public function getItemsByName($name)
    {
        
    }
    public function update_dependent_properties($objs)
    {
        
    }        
    public function save_new()
    {
        
    }        
    public function update_properties($data)
    {
        
    }        
}
