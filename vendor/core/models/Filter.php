<?php
namespace Dcs\Vendor\Core\Models;

use Exception;

class Filter
{
    protected $prop;
    protected $val;
    protected $valmin;
    protected $valmax;
    public function __construct($prop, $val, $valmin='',$valmax='')
    {
	if (!$prop) {
            throw new DcsException("empty propid Filter");
	}
        $this->prop = $prop; 
        $this->val = $val; 
        $this->valmin = $valmin; 
        $this->valmax = $valmax; 
    }
    public function getname()
    {
        return $this->prop['name'];
    }
    public function getval()
    {
        return $this->val;
    }
    public function getprop()
    {
        return $this->prop;
    }
    public function getmdid()
    {
        return $this->prop['mdid'];
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
        $rowname = $prefix.str_replace(" ","",strtolower($this->prop['name'])).$postfix;
        $strwhere='';
        $fval = $this->val;
        $type = $this->prop['name_type'];
        if ($type == 'date')
        {
            $rowname = "to_char($rowname,'YYYY-MM-DD')";
        }    
        $parname = 'par_'.str_replace("-","_",$this->prop['id']);
        if ($fval != '') {    
            if (is_array($fval)) {
                if (count($fval) > 1) {    
                    $strwhere = " AND $rowname in :$parname";
                    if (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
                        $strval = "('".implode("','", $fval)."')"; 
                    } else {
                        $strval = "(".implode(",", $fval).")"; 
                    }    
                    $params[$parname] = $strval;
                } else {
                    $strwhere =" AND $rowname = :$parname";
                    $params[$parname] = $fval[0];
                }
            } else {
                $strwhere =" AND $rowname = :$parname";
                $params[$parname] = $fval;
            }
        } else {
            $fmin = $this->valmin;
            $fmax = $this->valmax;
            if (($fmin != '')||($fmax != '')) {
                if ($fmin) {
                    $par = $parname.'_min';
                    $strwhere .=" AND $rowname>= :$par";
                    $params[$par] = $fmin;
                }
                if ($fmax) {
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
        foreach ($arr_filter as $filter) {
            if ($filter instanceof Filter) {
                $strwhere .= $filter->get_findstr($prefix,$postfix,$params);
            }    
        }    
        return $strwhere;
    }
}
