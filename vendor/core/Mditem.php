<?php
namespace tzVendor;
use PDO;

class Mditem extends Model {
    public function __construct($mditem='') 
    {
	if ($mditem=='') 
        {
            throw new Exception("empty mditem");
	}
        $res = self::getMDitem($mditem);
        $this->id = $mditem; 
        $this->name = $res['name']; 
        $this->synonym = $res['synonym']; 
        $this->version = time();
    }
    function get_data($mode='') 
    {
      return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'ardata'=>array(
              'id'=>$this->id,
              'name'=>$this->name,
              'synonym'=>$this->synonym,
          ),
          'plist' => array(
                        'id'=>'ID',
                        'name'=>'NAME',
                        'synonym'=>'SYNONYM',
                    ),   
          'navlist' => array(
              $this->id=>$this->synonym
            ),
          'actionlist'=>array()
          );
    }

    public static function getMDitem($itemid) 
    {
        $sql = "SELECT ct.id, ct.name, ct.synonym FROM \"CTable\" as ct 
        	LEFT JOIN \"MDTable\" as md
                ON ct.mdid=md.id AND md.name= :namemditems WHERE ct.id=:itemid LIMIT 1";
        
	$res = DataManager::dm_query($sql,array('namemditems'=>'MDitems','itemid'=>$itemid)); 
        return  $res->fetch(PDO::FETCH_ASSOC);
    }
    public static function getMDitemByName($name)
    {
        $sql = "SELECT ct.id, ct.name, ct.synonym  FROM \"CTable\" as ct 
        	INNER JOIN \"MDTable\" as mc 
                ON ct.mdid=mc.id AND mc.name= :namemditems WHERE ct.name=:name LIMIT 1";
        
        $res = DataManager::dm_query($sql,array('namemditems'=>'MDitems','name'=>$name));        
        return $res->fetch(PDO::FETCH_ASSOC);
    }
    public static function getAllMDitems() {
        $sql = "SELECT ct.id, ct.name, ct.synonym  FROM \"CTable\" as ct 
        	INNER JOIN \"MDTable\" as mc 
                ON ct.mdid=mc.id AND mc.name= :namemditems";
        $res = DataManager::dm_query($sql,array('namemditems'=>'MDitems'));        
	return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllMDbyItem($mditem) {
	$sql = "SELECT id, name, synonym FROM \"MDTable\" AS mdt WHERE mdt.mditem= :mditem";
        $sth = DataManager::dm_query($sql,array('mditem'=>$mditem));        
        $objs = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }
}

