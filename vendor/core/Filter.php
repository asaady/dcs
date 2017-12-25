<?php
namespace tzVendor;
use tzVendor\Mdproperty;

class Filter
{
    protected $mdid;
    protected $propid;
    protected $type;
    protected $val;
    protected $name;
    protected $valmin;
    protected $valmax;
    public function __construct($propid, $val, $valmin='',$valmax='')
    {
	if ($propid=='') {
            throw new Exception("empty propid Filter");
	}
        $arprop = Mdproperty::getProperty($propid);
        $this->propid = $propid; 
        $this->mdid = $arprop['mdid']; 
        $this->name = $arprop['name']; 
        $this->type = $arprop['type']; 
        $this->val = $val; 
        $this->valmin = $valmin; 
        $this->valmax = $valmax; 
    }
    public function getname()
    {
        return $this->name;
    }
    public function getval()
    {
        return $this->val;
    }
    public function getpropid()
    {
        return $this->propid;
    }
    public function getmdid()
    {
        return $this->mdid;
    }
    public function getvalmin()
    {
        return $this->valmin;
    }
    public function getvalmax()
    {
        return $this->valmax;
    }
    public function get_findstr($prefix,$postfix,&$params) 
    {
        $rowname = $prefix.str_replace(" ","",strtolower($this->name)).$postfix;
        $strwhere='';
        $fval = $this->val;
        if ($this->type=='date')
        {
            $rowname = "to_char($rowname,'YYYY-MM-DD')";
        }    
        $parname = 'par_'.str_replace("-","_",$this->propid);
        if ($fval!='')
        {    
            if (is_array($fval))
            {
                if (count($fval)>1)
                {    
                    $strwhere = " AND $rowname in :$parname";
                    if (($this->type=='id')||($this->type=='cid')||($this->type=='mdid'))
                    {
                        $strval = "('".implode("','", $fval)."')"; 
                    }    
                    else
                    {
                        $strval = "(".implode(",", $fval).")"; 
                    }    
                    $params[$parname] = $strval;
                }    
                else 
                {
                    $strwhere =" AND $rowname = :$parname";
                    $params[$parname] = $fval[0];
                }
            }    
            else 
            {
                $strwhere =" AND $rowname = :$parname";
                $params[$parname] = $fval;
            }
        }
        else
        {
            $fmin = $this->valmin;
            $fmax = $this->valmax;
            if (($fmin!='')||($fmax!=''))
            {
                if ($fmin)
                {
                    $par = $parname.'_min';
                    $strwhere .=" AND $rowname>= :$par";
                    $params[$par] = $fmin;
                }
                if ($fmax)
                {
                    $par = $parname.'_max';
                    $strwhere .=" AND $rowname <= :$par";
                    $params[$par] = $fmax;
                }
            }
        } 
        return $strwhere;
    }
    public static function getstrwhere($arr_filter,$prefix,$postfix,&$params) 
    {
        $strwhere = '';
        foreach ($arr_filter as $filter)
        {
            if ($filter instanceof Filter)
            {
                $strwhere .= $filter->get_findstr($prefix,$postfix,$params);
            }    
        }    
        return $strwhere;
    }
}
