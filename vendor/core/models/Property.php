<?php
namespace Dcs\Vendor\Core\Models;

class Property extends Model
{
    protected $type;
    protected $length;
    protected $prec;
    protected $rank;
    protected $ranktostring;
    protected $ranktoset;
    protected $valmdid;
    protected $valmdname;
    protected $valmdtypename;
        
    function __construct($arData) {
        $this->id = $arData['id'];
        $this->name = $arData['name'];    
        $this->synonym = $arData['synonym'];  
        $this->type = $arData['type'];    
        $this->length = $arData['length'];    
        $this->prec = $arData['prec'];
        $this->rank = $arData['rank'];        
        $this->ranktostring = $arData['ranktostring'];
        $this->valmdid = $arData['valmdid'];
        $this->valmdname = $arData['valmdname'];
        $this->valmdtypename = $arData['valmdtypename'];
    }

    public function gettype() 
    {
      return $this->type;
    }
    public function getvalmdid() 
    {
      return $this->valmdid;
    }
    public function getvalmdname() 
    {
      return $this->valmdname;
    }
    public function getvalmdtypename() 
    {
      return $this->valmdtypename;
    }
    public function getlength() 
    {
      return $this->length;
    }
    public function getprec() 
    {
      return $this->prec;
    }
    public function getranktoset() 
    {
      return $this->ranktoset;
    }
    public function getranktostring() 
    {
      return $this->ranktostring;
    }
    public function getrank() 
    {
      return $this->rank;
    }
    function get_data($context) 
    {
        return array('id'=>$this->id,      
                    'version'=>$this->version,
                    'PLIST'=>array( 
                        array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'hidden','field'=>1),
                        array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'type'=>'cid','valmdid'=>$this->propid,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'type'=>'str','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE','rank'=>13,'type'=>'bool','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER','rank'=>14,'type'=>'bool','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                        array('id'=>'isdepend','name'=>'isdepend','synonym'=>'IS DEPENDENT','rank'=>15,'type'=>'bool','valmdid'=>DCS_EMPTY_ENTITY,'valmdtypename'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1)
                    ),
                    'navlist'=>array(
                    $this->id=>$this->synonym
                    )
              );

    }
}

