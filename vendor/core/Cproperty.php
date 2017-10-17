<?php
namespace tzVendor;
use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class Cproperty extends Model {
    protected $collectionset;
    protected $type;
    protected $length;
    protected $prec;
    protected $rank;
    protected $ranktoset;
    protected $valmdid;
    protected $valmdname;
    
    public function __construct($id) 
    {
        if ($id=='')
        {
            die("class.CProperty constructor: id is empty");
        }
        
        $arData = self::getCProperty($id);
        if ($arData)
        {
            //передан id реального свойства метаданного
            $mdid = $arData['mdid'];
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];  
            $this->type = $arData['type'];    
            $this->length = $arData['length'];    
            $this->prec = $arData['prec'];
            $this->rank = $arData['rank'];        
            $this->ranktoset = $arData['ranktoset'];
            $this->valmdid = $arData['valmdid'];
            $this->valmdname = $arData['valmdname'];
        } 
        else 
        {
            //считаем что передан id реального метаданного и создаем пустое свойство
            $mdid = $id;
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->type = 'str';    
            $this->length = 10;    
            $this->prec = 0;    
            $this->rank = 999;    
            $this->ranktoset = 0;    
        }
        $this->collectionset = new CollectionSet($mdid);
        $this->version=time();
    }
    function getcollectionset()
    {
        return $this->collectionset;
    }
    function gettype() 
    {
      return $this->type;
    }
    function getvalmdid() 
    {
      return $this->valmdid;
    }
    function getlength() 
    {
      return $this->length;
    }
    function getprec() 
    {
      return $this->prec;
    }
    function getranktoset() 
    {
      return $this->ranktoset;
    }
    function getrank() 
    {
      return $this->rank;
    }
    function get_data($mode='') 
    {
        $valmdid = '';
        $valmdtype = '';
        $valmdtypename = '';
        $valmdname = '';
        if ($this->valmdid!='')
        {
            $valmdentity = new Mdentity($this->valmdid);
            $valmdid = $valmdentity->getid();
            $valmdtype = $valmdentity->gettype();
            $valmdtypename = $valmdentity->getmdtypename();
            $valmdname = $valmdentity->getname();
        }
        if ($this->id=='')
        {    
            $navlist = array(
                        $this->collectionset->getmditem()->getid()=>$this->collectionset->getmditem()->getsynonym(),
                        $this->collectionset->getid()=>$this->collectionset->getsynonym(),
                        $this->id=>'Новый'
                        );
        }
        else
        {
            $navlist = array(
                        $this->collectionset->getmditem()->getid()=>$this->collectionset->getmditem()->getsynonym(),
                        $this->collectionset->getid()=>$this->collectionset->getsynonym(),
                        $this->id=>$this->synonym
                        );
        }    
        return array('id'=>$this->id,      
                    'name'=>$this->name,
                    'synonym'=>$this->synonym,
                    'version'=>$this->version,
                    'type'=>$this->type,
                    'length'=>$this->length,
                    'prec'=>$this->prec,
                    'rank'=>$this->rank,
                    'ranktoset'=>$this->ranktoset,
                    'valmdid'=>$valmdid,
                    'valmdtype'=>$valmdtype,
                    'valmdname'=>$valmdname,
                    'valmdtypename'=>$valmdtypename,
                   'PLIST'=>array(
                       array('id'=>'id','name'=>'ID','rank'=>0,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'name', 'name'=>'NAME','rank'=>1,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'synonym','name'=>'SYNONYM','rank'=>3,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'type','name'=>'TYPE','rank'=>4,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'length','name'=>'LENGTH','rank'=>5,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'prec','name'=>'PREC','rank'=>6,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'rank','name'=>'RANK','rank'=>7,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'ranktoset','name'=>'RANKTOSTRING','rank'=>9,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'valmdtype','name'=>'VALMDTYPE','rank'=>11,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'valmdid','name'=>'VALMDID','rank'=>13,'type'=>'mdid','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'valmdname','name'=>'VALMDNAME','rank'=>14,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY),
                       array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','rank'=>14,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY)
                   ),
                    'navlist'=>$navlist
              );

    }
    function loadData($mode,$edit_mode) 
    {
        $valmdid = '';
        $valmdtypename = '';
        $valmdname = '';
        if ($this->valmdid!='')
        {
            $valmdentity = new Mdentity($this->valmdid);
            $valmdid = $valmdentity->getid();
            $valmdtypename = $valmdentity->getmdtypename();
            $valmdname = $valmdentity->getname();
        }
        $objs=array();
        $objs['PLIST']=array();
        $objs['PLIST']['id']=array('id'=>'id','class'=>'hidden');
        $objs['PLIST']['name']=array('id'=>'name','class'=>'active');
        $objs['PLIST']['synonym']=array('id'=>'synonym','class'=>'active');
        $objs['PLIST']['valmdid']=array('id'=>'valmdid','class'=>'active');
        $objs['PLIST']['valmdname']=array('id'=>'valmdname','class'=>'active');
        $objs['PLIST']['valmdtype']=array('id'=>'valmdtype','class'=>'active');
        $objs['PLIST']['valmdtypename']=array('id'=>'valmdtypename','class'=>'active');
        $objs['PLIST']['rank']=array('id'=>'rank','class'=>'active');
        $objs['PLIST']['ranktoset']=array('id'=>'ranktoset','class'=>'active');
        $objs['PLIST']['length']=array('id'=>'length','class'=>'active');
        $objs['PLIST']['prec']=array('id'=>'prec','class'=>'active');
        $objs['PLIST']['type']=array('id'=>'type','class'=>'active');
        $objs['SDATA']=array();
        $objs['SDATA'][$this->id]=array();
        $objs['SDATA'][$this->id]['id']=array('id'=>'','name'=>$this->id);
        $objs['SDATA'][$this->id]['name']=array('id'=>'','name'=>$this->name);
        $objs['SDATA'][$this->id]['synonym']=array('id'=>'','name'=>$this->synonym);
        $objs['SDATA'][$this->id]['type']=array('id'=>'','name'=>$this->type);
        $objs['SDATA'][$this->id]['length']=array('id'=>'','name'=>$this->length);
        $objs['SDATA'][$this->id]['prec']=array('id'=>'','name'=>$this->prec);
        $objs['SDATA'][$this->id]['rank']=array('id'=>'','name'=>$this->rank);
        $objs['SDATA'][$this->id]['ranktoset']=array('id'=>'','name'=>$this->ranktoset);
        $objs['SDATA'][$this->id]['valmdid']=array('id'=>$valmdid,'name'=>$valmdname);
        $objs['SDATA'][$this->id]['valmdtypename']=array('id'=>'','name'=>$valmdtypename);
        $objs['actionlist'] = DataManager::getActionsbyItem('Cproperty',$mode,$edit_mode);
        return $objs;
     }
    function update($data) {
        $sql = '';
        $objs = $this->before_save($data);
        $params = array();
        foreach($objs as $row)
        {    
           $sql .= ", $row[name]= :$row[name]";
           $params[$row['name']] = $row['nval'];
           if ($row['name']=='valmdid')
           {
               $params[$row['name']] = $row['nvalid'];
           }    
        }
        $objs['status']='NONE';
        if ($sql!=''){
            $objs['status']='OK';
            $sql = substr($sql,1);
            $id = $this->id;
            $sql = "UPDATE \"CProperties\" SET$sql WHERE id=:id";
            $params['id'] = $id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                return array('status'=>'ERROR', 'msg'=>$sql, 'id'=>'');
            }
        }
        return array('status'=>'OK', 'id'=>$this->id);
    }
    function create($data) {
        
        $plist = array('id','name','synonym','type','length','prec','rank','ranktoset','valmdid');
        $flds = '';
        $params = array();
        foreach($plist as $fname)
        {    
            if (array_key_exists($fname, $data))
            {
                $val = $data[$fname]['name'];
                if ($val!='')
                {
                    if ($fname=='valmdid')
                    {
                       $val = $data[$fname]['id'];
                    }    
                }    
                if ($val!='')
                {    
                    $params[$fname] = $val;
                    $flds .= ', '.$fname;
                    $vals .= ', :'.$fname;
                }    
            }
        }
        $objs = array();
        $objs['id']='';
        $objs['status']='NONE';
        if ($flds!='')
        {
            $flds = substr($flds, 1);
            $vals = substr($vals, 1);
            $sql = "INSERT INTO \"CProperties\" (".$flds.",mdid) VALUES (".$vals.", :mdid) RETURNING \"id\"";
            $params['mdid'] = $this->collectionset->getid();
            
            $objs['status']='ERROR';
            $objs['msg']=$sql;
            $res = DataManager::dm_query($sql,$params);
            if($res) 
            {
                $row = $res->fetch(PDO::FETCH_ASSOC);
                if($row) 
                {
                    $objs['status']='OK';
                    $objs['id']=$row['id'];
                }    
            }
        }
        return $objs;
    }
    function before_save($data) {
        $plist = array('id','name','synonym','type','length','prec','rank','ranktoset','valmdid');
        $objs = array();
        foreach($plist as $fname)
        {    
            $pval = call_user_func(array($this, 'get' . $fname));
            if (array_key_exists($fname, $data))
            {
                if ($fname=='valmdid')
                {
                    $nval = $data[$fname]['id'];
                    $nvalid=$data[$fname]['id'];
                    $nvalname=$data[$fname]['name'];
                    $pvalname=$this->valmdname;
                }    
                else 
                {
                    $nval = $data[$fname]['name'];
                    $nvalid='';
                    $nvalname=$data[$fname]['name'];
                    $pvalname=$pval;
                }
            }
            if ($pval==$nval)
            {
                continue;
            }    
            $objs[]=array('name'=>$fname, 'pval'=>$pvalname, 'nval'=>$nvalname, 'nvalid'=>$nvalid);
        }
	return $objs;
    }
        
    public static function getCProperty($propid) 
    {
        $objs = array();
        $sql = DataManager::get_select_cproperties(" WHERE mp.id = :propid");
	$res = DataManager::dm_query($sql,array('propid'=>$propid));
        return $res->fetch(PDO::FETCH_ASSOC);
    }
    public static function IsExistTheProp($mcid,$propid) 
    {
	$sql = "SELECT 	mp.synonym, mp.rank FROM \"CProperties\" as mp
		WHERE mp.mcid=:mcid and mp.id=:propid";	
	$res = DataManager::dm_query($sql,array('mcid'=>$mcid,'propid'=>$propid));
	return ((bool)$res->fetch(PDO::FETCH_ASSOC));
    }
    public static function createMDProperty($data) {
	$sql = "INSERT INTO \"CProperties\" (name, synonym, mdid, length, prec, rank, ranktoset, valmdid, valmcid) VALUES (:name, :synonym, :mdid, :length, :prec, :rank, :ranktoset, :valmdid, :valmcid) RETURNING \"id\"";
	$res = DataManager::dm_query($sql,$data);
        return $res->fetch(PDO::FETCH_ASSOC);
    }
}

