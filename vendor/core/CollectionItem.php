<?php
namespace tzVendor;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
use PDO;
use tzVendor\CollectionSet;

class CollectionItem extends Model {
    protected $collectionset;
    
    function __construct($id) {
        if ($id==''){
            die("class.CollectionItem constructor: id is empty");
        }
        $arData = CollectionSet::getCollectionItemByID($id);
        if ($arData){
            //передан id реального элемента коллекции
            $this->id = $arData['id'];
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];    
            $setid = $arData['setid'];    
	}else {
            //передан id реальной коллекции
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $setid = $id;    
	}
        $this->collectionset = new CollectionSet($setid);
        $this->version=time();
 }
    
    function get_data($mode='') {
        
        $plist = MdpropertySet::getMDProperties($this->collectionset->getid(),$mode," WHERE mp.mdid = :mdid ");
        if ($this->id=='')
        {    
            $navlist = array($this->collectionset->getmditem()->getid()=>$this->collectionset->getmditem()->getsynonym(),
                       $this->collectionset->getid()=>$this->collectionset->getsynonym(),
                       $this->id=>'Новый'
                   );
        }
        else
        {
            $navlist = array($this->collectionset->getmditem()->getid()=>$this->collectionset->getmditem()->getsynonym(),
                       $this->collectionset->getid()=>$this->collectionset->getsynonym(),
                       $this->id=>$this->synonym
                   );
        }    
      
      return array('id'=>$this->id,      
                   'name'=>$this->name,
                   'synonym'=>$this->synonym,
                   'version'=>$this->version,
                   'PLIST' => $plist,
                   'setdata'=>array(),
                   'navlist'=>$navlist
              );

    }
    function getData($mode='',$edit_mode='') 
    {
        $objs = array();
        $objs['PLIST'] = MdpropertySet::getMDProperties($this->collectionset->getid(),$mode," WHERE mp.mdid = :mdid ",true);
        $objs['SDATA'] = array();
        $objs['actionlist']= DataManager::getActionsbyItem('CollectionItem',$mode,$edit_mode);
        $sql = "SELECT pt.id, pt.name, pt.synonym, pt.mdid, pt.type FROM \"CProperties\" AS pt WHERE pt.mdid = :mdid";
        $sth = DataManager::dm_query($sql,array('mdid'=>$this->collectionset->getid()));        
        $join = " FROM \"CTable\" AS ct";
        $params = array();
        $plist = array();
        $sql = 'SELECT ct.id, ct.name, ct.synonym, ct.mdid';
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $rowname = str_replace("  ","",$row['name']);
            $rowname = str_replace(" ","",$rowname);
            $rowtype = $row['type'];
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
            $plist[] = $row;
        }        
        
        $sql = $sql.$join." WHERE ct.id = :id";
        $params['id']=$this->id;
        $sth = DataManager::dm_query($sql,$params);        
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs['SDATA'][$row['id']] = array();
            $objs['SDATA'][$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
            $objs['SDATA'][$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
            $objs['SDATA'][$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
            $objs['SDATA'][$row['id']]['mdid'] = array('id'=>'','name'=>$row['mdid']);
            foreach($plist as $prow)
            {
                $rowname = str_replace("  ","",$prow['name']);
                $rowname = str_replace(" ","",$rowname);
                $objs['SDATA'][$row['id']][$prow['id']] = array('id'=>$row["id_$rowname"],'name'=>$row["name_$rowname"]);
            }    
        }
        return $objs;
    }
    function update($data) 
    {
        if ($this->collectionset->getname()=='Users')
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
        return array($this->id=>array('id'=>$this->id,'name'=>"Элемент коллекции ".$this->collectionset->getsynonym(),'pval'=>$this->synonym,'nval'=>'Удалить'));
    }    
    function delete() {
        $sql = "DELETE FROM \"CTable\" WHERE id=:id";
        $params = array();
        $params['id'] = $this->id;
        $res = DataManager::dm_query($sql,$params);        
        $ares = array('status'=>'OK', 'id'=>$this->collectionset->getid());
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
            if ($this->collectionset->getname()=='Users')
            {
                $user = new User;
                $ares = $user->create($data);
            }    
            else 
            {
                $sql ="INSERT INTO \"CTable\" (name, synonym, mdid) VALUES (:name, :synonym, :mdid) RETURNING \"id\"";
                $params = array('name' => $curname, 'synonym'=>$data['synonym']['name'],'mdid'=> $this->collectionset->getid());
                $res = DataManager::dm_query($sql,$params);
                $row = $res ->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $ares = array('status'=>'OK', 'id'=>$id);
                $sql = "SELECT pt.id, pt.name, pt.synonym, pt.mdid, pt.type FROM \"CProperties\" AS pt WHERE pt.mdid = :mdid";
                $res = DataManager::dm_query($sql,array('mdid'=>$this->collectionset->getid()));        
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
    
    function getcollectionset()
    {
        return $this->collectionset;
    }
    function setcollectionset($collectionset)
    {
        $this->collectionset = $collectionset;
    }

}
