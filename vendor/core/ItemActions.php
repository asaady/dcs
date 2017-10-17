<?php
namespace tzVendor;
use tzVendor\DataManager;
use PDO;

class ItemActions {
    protected $id;
    protected $actionid;
    protected $actionname;
    protected $mditem;
    protected $mditemname;
    protected $rank;
    protected $toset;
    protected $activity;
    protected $version;
    
    public function __construct($id) {
        
        if ($id==''){
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->actionid = '';    
            $this->actionname = '';    
            $this->mditem = '';    
            $this->itemname = '';    
            $this->rank = 99;    
            $this->toset = false;        
        } else {
            $arData = self::ItemActionsSelectByID($id);
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];    
            $this->mditem = $arData['mditem'];    
            $this->mditemname = $arData['mditemname'];    
            $this->actionid = $arData['actionid'];
            $this->actionname = $arData['actionname'];
            $this->rank = $arData['rank'];
            $this->toset = $arData['toset'];
            $this->activity = $arData['activity'];
        }
        $this->dbtable='ItemActions';
        $this->version = time();
        
    }
    function get_data() {
      $Cols = Mditem::getMDitemByName('Cols');
      $iactions = CollectionSet::getMDCollectionByName('itemactions');
      return array('id'=>$this->id,      
                    'name'=>$this->name,
                    'synonym'=>$this->synonym,
                    'version'=>$this->version,
                    'mditem'=>$this->mditem,
                    'mditemname'=>$this->mditemname,
                    'actionid'=>$this->actionid,
                    'actionname'=>$this->actionname,
                    'rank'=>$this->rank,
                    'toset'=>$this->toset,
                    'activity'=>$this->activity,
                    'navlist'=>array(
                    $Cols['id']=>$Cols['synonym'],
                    $iactions['id']=>$iactions['synonym'],    
                    $this->id=>$this->synonym
                    ),
                  'actionlist'=>array(
                      array('id'=>'d6563e46-759d-4c01-b7b7-0ecf40140fbb','name'=>'save','synonym'=>'Записать', 'icon'=>'save')
                    )
                    );

     }
    function getid() {
      return $this->id;
     }
    function getname() {
      return $this->name;
     }
    function getsynonym() {
      return $this->synonym;
     }
    function setid($val) {
      if ($this->id=='') 
	$this->id=$val;
      else
	throw new Exception('You may not alter the value of the ID field!');
    }
    function setname($name) {
	$this->name=$name;
    }
    function setsynonym($val) {
	$this->synonym=$val;
    }
    function validate() {
    }
    public static function ItemActionsSelectByID($id){
          $sql = "SELECT ia.id,ia.name,ia.synonym,ia.mditem, it.name as mditemname, ia.actionid, ac.name as actionname, ia.rank, ia.toset, ia.activity FROM \"ItemActions\" as ia"
                  . " INNER JOIN \"MDitems\" as it ON ia.mditem=it.id"
                  . " INNER JOIN \"Actions\" as ac ON ia.actionid=ac.id WHERE ia.id=:id";
          $res = DataManager::dm_query($sql,array('id'=>$id));
          return $res->fetch(PDO::FETCH_ASSOC);
    }
}

