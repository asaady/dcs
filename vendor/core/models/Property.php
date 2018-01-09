<?php
namespace Dcs\Vendor\Core\Models;

//use Dcs\Vendor\Core\Model\Model;
//use Dcs\Vendor\Core\Model\iModel as iModel;
//
interface iProperty extends iModel
{
    public function getvalmdid();
    public function getvalmdname();
    public function getvalmdtypename();
    public function gettype(); 
    public function getlength();
    public function getprec();
    public function getranktoset();
    public function getranktostring();
    public function getrank();
}

class Property extends Model implements iProperty 
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
}

