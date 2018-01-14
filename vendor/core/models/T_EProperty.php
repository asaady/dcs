<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_EProperty {
    public function txtsql_getproperties($strwhere='') 
    {
        if ($strwhere == '') {
            $strwhere = ' WHERE mp.mdid = :mdid ';
        }
        $sql = "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, 
                       mp.synonym, pst.value as typeid, pt.name as type, 
                       mp.length, mp.prec, mp.mdid, mp.rank, mp.ranktostring, 
                       mp.ranktoset, mp.isedate, mp.isenumber, mp.isdepend, 
                       pmd.value as valmdid, valmd.name AS name_valmdid, 
                       valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
                       mi.name as valmdtypename FROM \"MDProperties\" AS mp
		  LEFT JOIN \"CTable\" as pr
		    LEFT JOIN \"CPropValue_mdid\" as pmd
        		INNER JOIN \"MDTable\" as valmd
                            INNER JOIN \"CTable\" as mi
                            ON valmd.mditem = mi.id
                        ON pmd.value = valmd.id
		    ON pr.id = pmd.id
		    LEFT JOIN \"CPropValue_cid\" as pst
                        INNER JOIN \"CProperties\" as cprs
                        ON pst.pid = cprs.id
                        AND cprs.name='type'
                        INNER JOIN \"CTable\" as pt
                        ON pst.value = pt.id
		    ON pr.id = pst.id
		  ON mp.propid = pr.id
		$strwhere
		ORDER BY rank";
        return $sql;
    }
    public function getplist($mode='') 
    {
        $plist = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','class'=>'active','field'=>1),
            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'type'=>'cid','class'=>'active','field'=>1),
            'name_propid'=>array('id'=>'name_propid','name'=>'name_propid','synonym'=>'NAME_PROPID','rank'=>2,'type'=>'str','class'=>'hidden','field'=>0),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>2,'type'=>'str','class'=>'readonly','field'=>0),
            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'int','class'=>'active','field'=>1),
            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'int','class'=>'active','field'=>1),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'int','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'type'=>'int','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'type'=>'int','class'=>'active','field'=>1),
            'isedate'=>array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE','rank'=>13,'type'=>'bool','class'=>'active','field'=>1),
            'isenumber'=>array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER','rank'=>14,'type'=>'bool','class'=>'active','field'=>1),
            'isdepend'=>array('id'=>'isdepend','name'=>'isdepend','synonym'=>'IS DEPENDENT','rank'=>15,'type'=>'bool','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>19,'type'=>'mdid','class'=>'readonly','field'=>0),
            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'type'=>'str','class'=>'hidden','field'=>0),
            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>21,'type'=>'str','class'=>'readonly','field'=>0),
             );
        if ($mode == 'CONFIG') {
            $plist['id']['class'] = 'readonly';
        }
        return $plist;
    }
    public function getMustBePropsUse()
    {
      	$sql = "SELECT ct_pt.name, ct_pt.synonym, pv_mditem.value as mditem, pv_pt.value as propid, pv_rank.value as rank, 
                    COALESCE(pv_edate.value, false) as isedate, COALESCE(pv_enum.value, false) as isenumber, ct_tp.name as type, COALESCE(pv_len.value,0) as length, COALESCE(pv_prc.value,0) as prec FROM \"CTable\" as pu 
	inner join \"MDTable\" as md
	ON pu.mdid = md.id
	and md.name='PropsUse'
	inner join \"CPropValue_cid\" as pv_mditem
		inner join \"CProperties\" as cp_mditem
		ON pv_mditem.pid=cp_mditem.id
		AND cp_mditem.name='mditem'
	ON pu.id=pv_mditem.id
        and pv_mditem.value = :mdtype
	inner join \"CPropValue_cid\" as pv_pt
		inner join \"CProperties\" as cp_pt
		ON pv_pt.pid=cp_pt.id
		AND cp_pt.name='propid'
                inner join \"CTable\" as ct_pt
                on pv_pt.value=ct_pt.id
		inner join \"CPropValue_cid\" as pv_tp
                    inner join \"CProperties\" as cp_tp
                    ON pv_tp.pid=cp_tp.id
                    AND cp_tp.name='type'
                    inner join \"CTable\" as ct_tp
                    on pv_tp.value = ct_tp.id
		on pv_pt.value = pv_tp.id
		left join \"CPropValue_int\" as pv_len
                    inner join \"CProperties\" as cp_len
                    ON pv_len.pid=cp_len.id
                    AND cp_len.name='length'
		on pv_pt.value = pv_len.id
		left join \"CPropValue_int\" as pv_prc
                    inner join \"CProperties\" as cp_prc
                    ON pv_prc.pid=cp_prc.id
                    AND cp_prc.name='prec'
		on pv_pt.value = pv_prc.id
        ON pu.id=pv_pt.id
	inner join \"CPropValue_int\" as pv_rank
		inner join \"CProperties\" as cp_rank
		ON pv_rank.pid=cp_rank.id
		AND cp_rank.name='rank'
        ON pu.id=pv_rank.id
	left join \"CPropValue_bool\" as pv_edate
		inner join \"CProperties\" as cp_edate
		ON pv_edate.pid=cp_edate.id
		AND cp_edate.name='isedate'
        ON pu.id=pv_edate.id
	left join \"CPropValue_bool\" as pv_enum
		inner join \"CProperties\" as cp_enum
		ON pv_enum.pid=cp_enum.id
		AND cp_enum.name='isenumber'
        ON pu.id=pv_enum.id";
	$res = DataManager::dm_query($sql,array('mdtype'=>$this->mditem->getid()));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
}            

