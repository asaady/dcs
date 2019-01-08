<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_EProperty {
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

