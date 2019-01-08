<?php
namespace Dcs\Vendor\Core\Models;

abstract class Property extends Sheet implements I_Sheet, I_Property {
    
    protected $propid;
    protected $name_propid;
    protected $type;
    protected $name_type;
    protected $length;
    protected $prec;
    protected $rank;
    protected $ranktoset;
    protected $ranktostring;
    protected $isedate;
    protected $isenumber;
    protected $isdepend;
    protected $valmdid;
    protected $name_valmdid;
    protected $valmdtypename;
    protected $field;
    protected $propstemplate;
    
    public function __construct($id)
    {
        if (!$id)
        {
            throw new DcsException("class.MDProperty constructor: id is empty");
        }
        
        $arData = $this->getProperty($id);
        if ($arData)
        {
            //передан id реального свойства метаданного
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];  
            $this->propid = $arData['propid'];
            $this->name_propid = $arData['name_propid'];
            $this->type = $arData['type'];    
            $this->name_type = $arData['name_type'];    
            $this->length = $arData['length'];    
            $this->prec = $arData['prec'];
            $this->rank = $arData['rank'];        
            $this->ranktostring = $arData['ranktostring'];
            $this->ranktoset = $arData['ranktoset'];
            $this->isedate = $arData['isedate'];
            $this->isenumber = $arData['isenumber'];
            $this->isdepend = $arData['isdepend'];
            $this->valmdid = $arData['valmdid'];
            $this->name_valmdid = $arData['name_valmdid'];
            $this->valmdtypename = $arData['valmdtypename'];
        } 
        else 
        {
            //считаем что передан id реального метаданного и создаем пустое свойство
            $mdid = $id;
            $this->propstemplate = new PropsTemplate('');
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->type = 'str';    
            $this->length = 10;    
            $this->prec = 0;    
            $this->rank = 999;    
            $this->ranktostring = 0;        
            $this->ranktoset = 0;    
            $this->isedate = false;        
            $this->isenumber = false;        
            $this->isdepend = false;
        }
        $this->version=time(); 
    }
}
