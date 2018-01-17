<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class CollectionItem extends Head implements I_Head, I_Property {
    use T_Item;
    use T_Collection;
    use T_CProperty;
    
    function item() {
        return NULL;
    }
    function head($mdid='') {
        return new CollectionSet($mdid);
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
                $res = DataManager::dm_query($sql,$params);
                if(!$res) 
                {
                    $objs['status']='ERROR';
                    $objs['msg']=$sql;
                    break;
                }
            }    
            
            foreach($pdata['PLIST'] as $row)
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
                    $valname = $prow[$id]['name'];
                    $dataid = $data[$id]['id'];
                    $valid = $prow[$id]['id'];
                    if (($row['type']=='id')||($row['type']=='cid')||($row['type']=='mdid')) 
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
                    $sql = "SELECT value FROM \"CPropValue_$row[type]\" WHERE id=:id and pid=:pid";
                    $params['id'] = $this->id;
                    $params['pid'] = $id;
                    $res = DataManager::dm_query($sql,$params);
                    $rw = $res->fetch(PDO::FETCH_ASSOC);
                    if ($rw)
                    {    
                        $sql = "UPDATE \"CPropValue_$row[type]\" SET value=:val, userid=:userid, dateupdate=DEFAULT WHERE id=:id and pid=:pid returning \"id\"";
                    }
                    else
                    {
                        $sql = "INSERT INTO \"CPropValue_$row[type]\" (id, pid, value, userid) VALUES (:id, :pid, :val, :userid) returning \"id\"";
                    }    
                    $params['val'] = $val;
                    $params['userid']=$_SESSION["user_id"];
                    
                    $res = DataManager::dm_query($sql,$params);
                    $rw = $res->fetch(PDO::FETCH_ASSOC);
                    if(!$rw) 
                    {
                        $objs['status']='ERROR';
                        $objs['msg']=$sql;
                        break;
                    }
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
    function before_save($data) {
        $pdata = $this->getData();
        $objs = array();
        foreach($pdata['SDATA'] as $prow)
        {    
            foreach($pdata['PLIST'] as $row)
            {
                $key = $row['name'];
                $id = $row['id'];
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
                    if (($row['type']=='id')||($row['type']=='cid')||($row['type']=='mdid')) 
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
                $sql ="INSERT INTO \"CTable\" (name, synonym, mdid) VALUES (:name, :synonym, :mdid) RETURNING \"id\"";
                $params = array('name' => $curname, 'synonym'=>$data['synonym']['name'],'mdid'=> $this->head->getid());
                $res = DataManager::dm_query($sql,$params);
                $row = $res ->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $ares = array('status'=>'OK', 'id'=>$id);
                $sql = "SELECT pt.id, pt.name, pt.synonym, pt.mdid, pt.type FROM \"CProperties\" AS pt WHERE pt.mdid = :mdid";
                $res = DataManager::dm_query($sql,array('mdid'=>$this->head->getid()));        
                $plist = $res ->fetchAll(PDO::FETCH_ASSOC);
                foreach ($plist as $f)
                {   
                    if (!array_key_exists($f['id'],$data)) continue;
                    $dataname= $data[$f['id']];
                    $type= $f['type'];
                    if ($dataname['name']=='')
                    {
                        continue;
                    }
                    $val = $dataname['name'];
                    if ($type=='bool') 
                    {
                        if ($val=='t')
                        {
                            $val = 'true';
                        }
                        if ($val!='true')
                        {
                            $val ='false';
                        }
                    } 
                    elseif (($type=='id')||($type=='cid')||($type=='mdid'))
                    {
                        $val = $dataname['id'];
                    }    

                    $sql = "INSERT INTO \"CPropValue_$type\" (id, pid, value) VALUES (:id, :pid, :value) RETURNING \"id\"";
                    $params = array();
                    $params['id'] = $id;
                    $params['pid'] = $f['id'];
                    $params['value'] = $val;
                    $res = DataManager::dm_query($sql,$params);        
                    if(!$res) {
                        $ares = array('status'=>'ERROR', 'msg'=>$sql);
                    }
                }    
            }
        }    
        return $ares;
    }
    public function getItemsByName($name)
    {
        
    }
}
