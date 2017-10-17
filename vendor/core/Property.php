<?php>
namespace tzVendor;

class MDProperty extends Model {
    protected $MDEntity;
    protected $type;
    protected $length;
    protected $prec;
    protected $rank;
    protected $periodic;
    protected $periodwidth;
    
    
    public function __construct($id, $mdid='') {
        if ($id=''){
            if ($mdid='') {
                die("class.MDProperty constructor: mdid is empty");
                throw new Exception("class.MDPropertySet constructor: mdid is empty");
            }    
            $this->MDEntity = new MDEntity($mdid);
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->propid = '';    
            $this->type = 'str';    
            $this->length = 10;    
            $this->prec = 0;    
            $this->rank = 999;    
            $this->periodic = false;        
            $this->periodwidth = '';        
            $this->ranktostring = 0;        
            $this->isedate = false;        
            $this->valmdid = '';        
            $this->valmdname = '';        
        }else {
            $arData = DataManager::getPropMDbyID($id);
            $this->MDEntity = new MDEntity($arData['mdid']);
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];    
            $this->propid = $arData['propid'];    
            $this->type = $arData['type'];    
            $this->length = $arData['proplength'];    
            $this->prec = $arData['propprec'];
            $this->rank = $arData['rank'];        
            $this->periodic = $arData['periodic'];
            $this->periodwidth = $arData['periodwidth'];
            $this->ranktostring = $arData['ranktostring'];
            $this->isedate = $arData['isedate'];
            $this->valmdid = $arData['valmdid'];
            $this->valmdname = $arData['valmdname'];
        }
    }
    function get_data() {
      return array('id'=>$this->id,      
                    'name'=>$this->name,
                    'synonym'=>$this->synonym,
                    'type'=>$this->type,
                    'propid'=>$this->propid,
                    'length'=>$this->length,
                    'prec'=>$this->prec,
                    'rank'=>$this->rank,
                    'periodic'=>$this->periodic,
                    'periodwidth'=>$this->periodwidth,
                    'ranktostring'=>$this->ranktostring,
                    'isedate'=>$this->isedate,
                    'valmdid'=>$this->valmdid,
                    'valmdname'=>$this->valmdname,
                    'navlist'=>array(
                    $this->MDEntity->getmditem()=>$this->MDEntity->getmditemsynonym(),
                    $this->MDEntity->getid()=>$this->MDEntity->getsynonym(),
                    $this->id=>$this->synonym
                    ),        
                   'actionlist'=>DataManager::getActionsbyItem($this->MDEntity->getmditem())          
                    );

     }
    function gettype() {
      return $this->type;
     }
    function settype($val) {
	$this->type=$val;
    }
}

