<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use PDOStatement;
use PDOException;
use Dcs\Vendor\Core\Models\DcsException;
use Dcs\Vendor\Core\Models\Db;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class DataManager {
    
    public static function dm_query($sql, $params = []) {
        $db = Db::getInstance();
        return $db->run($sql, $params);
    }

    public static function dm_beginTransaction() {
        $db = Db::getInstance();
        $db->beginTransaction();
    }

    public static function dm_commit() {
        $db = Db::getInstance();
        $db->commit();
    }

    public static function dm_rollback() {
        $db = Db::getInstance();
        $db->rollBack();
    }

    public static function getMainSettingsByName($name) {
        $sql = "SELECT name, id, synonym, description FROM \"MainSettings\" WHERE name= :name";
        $sth = self::dm_query($sql, array('name' => $name));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    public static function getActionsbyItem($classname, $prefix = '', $action = '') {
        $sql = "SELECT ia.id, ct_icon.name, ct_icon.synonym, pv_icon.value as icon FROM \"CTable\" as ia 
	inner join \"MDTable\" as md
	ON ia.mdid = md.id
	and md.name='itemactions'
	inner join \"CPropValue_cid\" as pv_class
		inner join \"CProperties\" as cp_class
		ON pv_class.pid=cp_class.id
		AND cp_class.name='classname'
		inner join \"CTable\" as ct_cls
		on pv_class.value = ct_cls.id
	ON ia.id=pv_class.id
	inner join \"CPropValue_int\" as pv_rank
		inner join \"CProperties\" as cp_rank
		ON pv_rank.pid=cp_rank.id
		AND cp_rank.name='rank'
	ON ia.id=pv_rank.id
	inner join \"CPropValue_bool\" as pv_mode
		inner join \"CProperties\" as cp_mode
		ON pv_mode.pid=cp_mode.id
		AND cp_mode.name='config_mode'
	ON ia.id=pv_mode.id
	inner join \"CPropValue_bool\" as pv_edit
		inner join \"CProperties\" as cp_edit
		ON pv_edit.pid=cp_edit.id
		AND cp_edit.name='edit_mode'
	ON ia.id=pv_edit.id
	inner join \"CPropValue_cid\" as pv_action
		inner join \"CProperties\" as cp_action
		ON pv_action.pid=cp_action.id
		and cp_action.name = 'actionid'
		inner join \"CPropValue_str\" as pv_icon
			inner join \"CProperties\" as cp_icon
			ON pv_icon.pid=cp_icon.id
			and cp_icon.name='icon'
		on pv_action.value = pv_icon.id
		inner join \"CTable\" as ct_icon
		on pv_action.value = ct_icon.id
	ON ia.id=pv_action.id
	where ct_cls.name = :class #prefix #action ORDER BY pv_rank.value";
        $params = array();
        $params['class'] = $classname;
        if ($prefix === 'CONFIG') {
            $sql = str_replace('#prefix', '', $sql);
        } else {
            $sql = str_replace('#prefix', 'AND NOT pv_mode.value', $sql);
        }
        if (($action === 'EDIT') || ($action === 'SET_EDIT') || ($action === 'CREATE') || ($action === 'CREATE_PROPERTY')) {
            $sql = str_replace('#action', '', $sql);
        } else {
            $sql = str_replace('#action', 'AND NOT pv_edit.value', $sql);
        }
        $sth = self::dm_query($sql, $params);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSubSystems() {
        $sql = "SELECT ia.name, ia.synonym, pv_menu.value as tomenu, pv_rank.value as rank, pv_item.value as id FROM \"CTable\" as ia 
	inner join \"MDTable\" as md
	ON ia.mdid = md.id
	and md.name='Subsystems'
	inner join \"CPropValue_bool\" as pv_menu
		inner join \"CProperties\" as cp_menu
		ON pv_menu.pid=cp_menu.id
		AND cp_menu.name='tomenu'
	ON ia.id=pv_menu.id
	inner join \"CPropValue_int\" as pv_rank
		inner join \"CProperties\" as cp_rank
		ON pv_rank.pid=cp_rank.id
		AND cp_rank.name='rank'
	ON ia.id=pv_rank.id
	inner join \"CPropValue_str\" as pv_item
		inner join \"CProperties\" as cp_item
		ON pv_item.pid=cp_item.id
		AND cp_item.name='itemid'
	ON ia.id=pv_item.id
	where pv_menu.value ORDER BY pv_rank.value";
        $sth = self::dm_query($sql);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function IsExistDataProp($propid) {
        $sql = "SELECT 	t.id,t.userupdate,t.dateupdate, mp.synonym, mp.rank FROM \"IDTable\" AS t 
		INNER JOIN \"MDProperties\" as mp
		ON t.propid=mp.id 
		WHERE t.propid=:propid LIMIT 1";
        return self::dm_query($sql, array('propid' => $propid))->rowCount() != 0;
    }

    public static function getItemData($entityid) {
        $sql = "SELECT sdl.childid as itemid FROM \"SetDepList\" as sdl
		WHERE sdl.parentid=:entityid";
        $sth = self::dm_query($sql, array('entityid' => $entityid));
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getstrwhere($filter, $type, $name, &$params,$colname='it',$parname='propid') 
    {
        $strwhere = '';
        if (!($filter instanceof Filter)) {
            throw new DcsException("it is not filter class",DCS_ERROR_WRONG_PARAMETER);
        }
        $fval = $filter->getval();
        $prop = $filter->getprop();
        $pid = $prop['id'];
        $strpid = str_replace('-', '', $pid);
        if ($fval != '') {
            switch ($type) {
                case 'date': $filterval = "$name>='" . substr($fval, 0, 10) . " 00:00:00+3' AND $name<='" . substr($fval, 0, 10) . " 23:59:59+3'";
                    break;
                default: $filterval = "$name=:par".$strpid;
                    $params['par'.$strpid] = $fval;
                    break;
            }
            $params['pid'.$strpid] = $pid;
            $strwhere .= " $filterval and $colname.$parname=:pid$strpid";
        }
        return $strwhere;
    }

    public static function createtemptable($sql, $tmpname, $params = []) {
        $sth = self::dm_query("CREATE TEMP TABLE $tmpname AS ($sql);", $params);
        return "$tmpname";
    }

    public static function get_select_entities($entities) 
    {
        $sql = "SELECT id, mdid FROM \"ETable\" as et WHERE id in $entities";
        return $sql;
    }

    public static function get_select_collections($entities) {
        $sql = "SELECT id, name, synonym, mdid FROM \"CTable\" as et WHERE id in $entities";
        return $sql;
    }

    public static function get_select_lastupdateForReq($count_req, $tt_t3, $tt_t0) {
        $sql = "SELECT $count_req as creq, t.id as tid, et.mdid, ts.entityid, ts.propid  FROM \"IDTable\" AS t INNER JOIN $tt_t3 AS ts  INNER JOIN $tt_t0 as et ON ts.entityid=et.id ON t.entityid=ts.entityid AND t.propid = ts.propid AND t.dateupdate=ts.dateupdate";
        return $sql;
    }

    public static function get_select_lastupdate($tt_id, $tt_pt) {
        $sql = "SELECT t.id as tid, t.userid, ts.dateupdate, ts.entityid, ts.propid, mp.type, mp.synonym AS pkey, mp.ranktostring, mp.isedate, mp.rank as rank
		FROM \"IDTable\" AS t 
		INNER JOIN $tt_id AS ts
                ON t.entityid=ts.entityid
		AND t.propid = ts.propid
		AND t.dateupdate=ts.dateupdate
		INNER JOIN $tt_pt as mp
		ON t.propid=mp.id
		ORDER BY entityid, rank";
        return $sql;
    }

    public static function get_select_maxupdate($tt_et, $tt_pt) {
        $sql = "SELECT max(dateupdate) AS dateupdate, entityid, propid  FROM \"IDTable\" WHERE entityid IN (SELECT et.id FROM $tt_et AS et) AND propid IN (SELECT pt.id FROM $tt_pt as pt) GROUP BY entityid, propid";
        return $sql;
    }

    public static function get_select_unique_mdid($tt_t0) {
        $sql = "SELECT DISTINCT mdid  FROM $tt_t0";
        return $sql;
    }

    public static function get_select_properties($strwhere) {
        $sql = "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, mp.synonym, 
            pst.value as typeid, pt.name as type, mp.length, mp.prec, mp.mdid, 
            mp.rank, mp.ranktostring, mp.ranktoset, mp.isedate, mp.isenumber, 
            mp.isdepend, pmd.value as valmdid, valmd.name AS valmdname, 
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
    public static function get_select_rproperties($strwhere) {
        $sql = "SELECT mp.id, mp.propid, pr.name as name_prpid, mp.name, mp.synonym, pst.value as typeid, pt.name as type, mp.length, mp.prec, mp.mdid, mp.rank, mp.ranktoset, mp.isres, pmd.value as valmdid, valmd.name AS valmdname, valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, mi.name as valmdtypename FROM \"RegProperties\" AS mp
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
    public static function get_select_cvalue($tt_id, $tt_pt) {
        $sql = "SELECT t.id as tid, t.userupdate, ts.dateupdate, ts.entityid, ts.propid, mp.type, mp.synonym AS pkey, mp.ranktostring, mp.isedate, mp.rank as rank
		FROM \"IDTable\" AS t 
		INNER JOIN $tt_id AS ts
                ON t.entityid=ts.entityid
		AND t.propid = ts.propid
		AND t.dateupdate=ts.dateupdate
		INNER JOIN $tt_pt as mp
		ON t.propid=mp.id
		ORDER BY entityid, rank";
        return $sql;
    }

    public static function droptemptable($arrtt) {
        $errormsg = '';
        foreach ($arrtt as $tt => $name) {
            $sql = "DROP TABLE $name";
            $sth = self::dm_query($sql);
        }
        return $errormsg;
    }

    public static function getPropForID($entityid) {
        $sql = "SELECT et.id, mp.id as mpid, mp.propid, mp.name, mp.synonym, ct_type.id as typeid, ct_type.name as type, mp.length, mp.prec, mp.rank, mp.ranktostring, mp.isedate, mp.isenumber, valmd.id as valmdid, valmd.name AS valmdname FROM \"ETable\" AS et 
		INNER JOIN \"MDProperties\" as mp
		  INNER JOIN \"CTable\" as pr
                    inner JOIN \"CPropValue_mdid\" as pv_mdid
                        inner join \"CProperties\" as cp_mdid
                        on pv_mdid.pid=cp_mdid.id
                        and cp_mdid.name='valmdid'
                        INNER JOIN \"MDTable\" as valmd
                        ON pv_mdid.value = valmd.id
                    ON pr.id=pv_mdid.id
                    inner JOIN \"CPropValue_cid\" as pv_type
                        inner join \"CProperties\" as cp_type
                        on pv_type.pid=cp_type.id
                        and cp_type.name='type'
                        INNER JOIN \"CTable\" as ct_type
                        ON pv_type.value = ct_type.id
                    ON pr.id=pv_type.id
		  ON mp.propid = pr.id
		ON et.mdid = mp.mdid
		WHERE et.id = :entityid ORDER BY mp.rank";


        $sth = self::dm_query($sql, array('entityid' => $entityid));
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPropByName($entityid, $name) {
        $sql = "SELECT mp.id as id, pr.name, mp.synonym FROM \"ETable\" AS et 
	      INNER JOIN \"MDProperties\" as mp
	      ON et.mdid = mp.mdid
              AND mp.name=:name
	      WHERE et.id = :entityid ORDER BY mp.rank";


        $sth = self::dm_query($sql, array('name' => $name, 'entityid' => $entityid));
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function isExistDepTemplate($id, $itemid) {
        $sql = "SELECT parentmdid, childmdid, type FROM \"DepTemplate\" WHERE parentmdid=:id AND childmdid=:itemid";
        $sth = self::dm_query($sql, array('id' => $id, 'itemid' => $itemid));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        $res = DCS_EMPTY;
        if (count($row)) {
            $res = $row['type'];
        }
    }

    public static function AddDepTemplate($id, $itemid, $type) {
        $sql = "INSERT INTO \"DepTemplate\" (parentmdid, childmdid, type) VALUES (:id,:itemid,:type)";
        $sth = self::dm_query($sql, array('id' => $id, 'itemid' => $itemid, 'type' => $type));
    }

    public static function UpdDepTemplate($id, $itemid, $type) {
        $sql = "UPDATE \"DepTemplate\" SET type=$type WHERE parentmdid=:id AND childmdid=:itemid";
        $sth = self::dm_query($sql, array('id' => $id, 'itemid' => $itemid));
    }

    public static function CreateDepTemplates($id, $itemid, $type) {
        $res = self::isExistDepTemplate($id, $itemid);
        if ($res == DCS_EMPTY) {
            self::AddDepTemplate($id, $itemid, $type);
        } else {
            if (!($res == $type)) {
                self::UpdDepTemplate($id, $itemid, $type);
            }
        }
    }

    public static function saveItemToSetDepList($parentid, $childid, $valrank = 0, &$errmsg = '') {
        $sql = "SELECT parentid, childid, rank FROM \"SetDepList\" WHERE parentid=:parentid AND childid=:childid";
        $sth = self::dm_query($sql, array('parentid' => $parentid, 'childid' => $childid));
        $rank = -1;
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $rank = $row['rank'];
            if ($rank > 0) {
                if (($valrank == $rank) || ($valrank == 0)) {
                    return $rank;
                }
            }
        }
        $maxrank = 0;
        if ($valrank == 0) {
            $maxrank = self::getMaxRankSetDepList($parentid, $errmsg);
            if ($maxrank < 0) {
                return -1;
            }
            $valrank = $maxrank + 1;
        }
        if ($rank == -1) {
            $sql = "INSERT INTO \"SetDepList\" (parentid, childid, rank) VALUES (:parentid,:childid,:rank)";
        } else {
            $sql = "UPDATE \"SetDepList\" SET rank=:rank WHERE parentid=:parentid AND childid=:childid";
        }
        $sth = self::dm_query($sql, array('parentid' => $parentid, 'childid' => $childid, 'rank' => $valrank));
        return $valrank;
    }

    public static function getMaxRankSetDepList($parentid, &$errmsg = '') {
        $sql = "SELECT max(sdl.rank) as maxrank FROM \"SetDepList\" as sdl INNER JOIN \"ETable\" as et ON sdl.childid=et.id WHERE parentid=:parentid";
        $sth = self::dm_query($sql, array('parentid' => $parentid));
        $maxrank = 0;
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $maxrank = $row['maxrank'];
        }
        return $maxrank;
    }

    public static function getParentidSetDepList($childid, &$parentid, &$errmsg = '') {
        $sql = "SELECT parentid, childid, rank FROM \"SetDepList\" WHERE childid=:childid";
        $sth = self::dm_query($sql, array('childid' => $childid));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (count($row)) {
            $parentid = $row['parentid'];
        }
        return 0;
    }

    public static function ResetItemSetDepList($childid, $val = 'false', &$errmsg = '') {
        $parentid = '';
        $res = self::getParentidSetDepList($childid, $parentid, $errmsg);
        if ($res < 0) {
            return $res;
        }
        if ($val == 'false') {
            $rank = 0;
        } else {
            $res = self::getMaxRankSetDepList($parentid, $errmsg);
            if ($res < 0) {
                return $res;
            }
            $valrank = $res + 1;
        }
        $res = self::saveItemToSetDepList($parentid, $childid, $valrank, $errmsg);
        if ($res < 0) {
            return $res;
        }
        return 0;
    }

    public static function FindRecord($tablename, $filter, $params) {
        $sql = "SELECT * FROM \"" . $tablename . "\" WHERE " . $filter;
        $sth = self::dm_query($sql, $params);
        $objs = array();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSetting($name) {
        $sql = "select ct.id, pv_set.value as id_settings, ct_set.name as name_settings, pv_prop.value as propid, pv_val.value as value, ct_type.name as type from \"CTable\" as ct "
                . "inner join \"MDTable\" as md "
                . "on ct.mdid=md.id "
                . "inner join \"CPropValue_cid\" as pv_usr "
                . "inner join \"CProperties\" as cp_usr "
                . "on pv_usr.pid = cp_usr.id "
                . "and cp_usr.name = 'user' "
                . "on ct.id=pv_usr.id "
                . "inner join \"CPropValue_cid\" as pv_set "
                . "inner join \"CProperties\" as cp_set "
                . "on pv_set.pid = cp_set.id "
                . "and cp_set.name = 'settings' "
                . "inner join \"CTable\" as ct_set "
                . "on pv_set.value = ct_set.id "
                . "and ct_set.name = :name "
                . "left join \"CPropValue_cid\" as pv_prop "
                . "inner join \"CProperties\" as cp_prop "
                . "on pv_prop.pid = cp_prop.id "
                . "and cp_prop.name = 'propstemplate' "
                . "inner join \"CPropValue_cid\" as pv_type "
                . "inner join \"CProperties\" as cp_type "
                . "on pv_type.pid = cp_type.id "
                . "and cp_type.name = 'type' "
                . "inner join \"CTable\" as ct_type "
                . "on pv_type.value = ct_type.id "
                . "on pv_prop.value = pv_type.id "
                . "on pv_set.value = pv_prop.id "
                . "on ct.id=pv_set.id "
                . "inner join \"CPropValue_str\" as pv_val "
                . "inner join \"CProperties\" as cp_val "
                . "on pv_val.pid = cp_val.id "
                . "and cp_val.name = 'value' "
                . "on ct.id=pv_val.id "
                . "where md.name='user_settings' and pv_usr.value = :userid";
        $params = array();
        $params['userid'] = $_SESSION['user_id'];
        $params['name'] = $name;
        $sth = self::dm_query($sql, $params);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row['value'];
        }
        return '';
    }

    public static function CopyTableSet($setid_src, $setid_dst, $user) {
        $sql = "SELECT s.childid, s.rank FROM \"SetDepList\" as s WHERE s.parentid=:setid_src";
        $sth = self::dm_query($sql, array('setid_src' => $setid_src));
        $trsql = "BEGIN";
        $trsth = self::dm_query($trsql);
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $newid = self::CopyEntity($row['childid'], $user);
            self::saveItemToSetDepList($setid_dst, $newid, $row['rank']);
        }
        $trsql = "COMMIT";
        $trres = self::dm_query($trsql);
        return 0;
    }

    public static function FindUser($login, $pass_hash) {
        $sql = "SELECT ct.id, ct.name, pvl.value as login, pvp.value as pass_hash  FROM \"CTable\" as ct 
	INNER JOIN \"CProperties\" as cpl 
		INNER JOIN \"CPropValue_str\" as pvl 
		ON cpl.id=pvl.pid AND pvl.value= :login
	ON ct.mcid=cpl.mcid AND cpl.name = :namelogin AND pvl.id = ct.id	
	INNER JOIN \"CProperties\" as cpp 
		INNER JOIN \"CPropValue_str\" as pvp 
		ON cpp.id=pvp.pid AND pvp.value= :pass_hash
	ON ct.mcid=cpp.mcid AND cpp.name = :namepass AND pvp.id = ct.id	
	INNER JOIN \"MDCollections\" as mc 
	ON ct.mcid=mc.id AND mc.name= :nameusers LIMIT 1";

        $res = self::dm_query($sql, array('login' => $login, 'pass_hash' => $pass_hash, 'namelogin' => 'login', 'namepass' => 'pass_hash', 'nameusers' => 'Users'));
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    public static function TableSelect($dbtable, $strwhere = '', $params = []) {
        if ($strwhere != '') {
            $strwhere = "WHERE $strwhere";
        }
        $sql = "SELECT * FROM \"$dbtable\" $strwhere";
        $res = self::dm_query($sql, $params);
        $objs = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }

    public static function isTableExist($dbtable) {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_name = :dbtable";
        $res = self::dm_query($sql, array('dbtable' => $dbtable));
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    public static function GetTableColumnsToSet($dbtable) {

        $sql = "SELECT  t.table_name, c.column_name, c.data_type "
                . "FROM information_schema.TABLES t JOIN information_schema.COLUMNS c ON t.table_name::text = c.table_name::text "
                . "WHERE t.table_schema::text = 'public'::text AND "
                . "t.table_catalog::name = current_database() AND "
                . "t.table_type::text = 'BASE TABLE'::text AND "
                . "NOT \"substring\"(t.table_name::text, 1, 1) = '_'::text AND "
                . "t.table_name = :dbtable "
                . "ORDER BY t.table_name, c.ordinal_position";
        $res = self::dm_query($sql, array('dbtable' => $dbtable));
        $objs = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['column_name']] = array('name' => $row['column_name'], 'synonym' => strtoupper($row['column_name']));
        }
        return $objs;
    }

    public static function GetTableColumns($dbtable) {
        $sql = "SELECT  t.table_name, c.column_name as name, c.column_name as id, c.column_name as synonym, c.data_type as type, row_number() OVER() as rank "
                . "FROM information_schema.TABLES t JOIN information_schema.COLUMNS c ON t.table_name::text = c.table_name::text "
                . "WHERE t.table_schema::text = 'public'::text AND "
                . "t.table_catalog::name = current_database() AND "
                . "t.table_type::text = 'BASE TABLE'::text AND "
                . "NOT \"substring\"(t.table_name::text, 1, 1) = '_'::text AND "
                . "t.table_name = :dbtable "
                . "ORDER BY t.table_name, c.ordinal_position";
        $res = self::dm_query($sql, array('dbtable' => $dbtable));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function CollectionItemUpdate($itemid, $data) {
        $row = self::getCollectionItemByID($itemid);
        $ires = '';
        $arow = $row->fetch(PDO::FETCH_ASSOC);
        $col = self::GetTableColumns($arow['dbtable']);
        $sql = '';
        foreach ($col as $f) {
            $name = $f['name'];
            if ($name == 'id')
                continue;
            if ($f['type'] == 'uuid') {
                if ($data[$name] == '') {
                    $data[$name] = DCS_EMPTY_ENTITY;
                }
            } elseif ($f['type'] == 'boolean') {
                if ($data[$name] == 't') {
                    $data[$name] = 'true';
                }
            }
            if ($data[$name] != '') {
                $sql .= ", $name='$data[$name]'";
            }
        }
        $sql = substr($sql, 1);
        $sql = "UPDATE \"$arow[dbtable]\" SET $sql WHERE id=:itemid";
        $res = self::dm_query($sql, array('itemid' => $itemid));
        $ares = array('status' => 'OK', 'id' => $itemid);
        return $ares;
    }

    public static function CollectionItemCreate($setid, $data) {
        $arow = self::getMDCollection($setid);
        $ires = '';
        if ($arow) {
            $col = self::GetTableColumns($arow['dbtable']);
            $fname = '';
            $arval = '';
            $curname = '';
            $err = '';
            foreach ($col as $f) {
                if ($f['name'] == 'name') {
                    $curname = $data['name'];
                }
            }
            if ($curname <> '') {
                $ardata = self::TableSelect($arow['dbtable']);
                foreach ($ardata as $d) {
                    $name = $d['name'];
                    if (trim($name) == trim($curname)) {
                        $err = "name is not unique";
                    }
                }
            } else {
                $err .= " Name is empty";
            }
            if ($err == '') {
                foreach ($col as $f) {
                    $name = $f['name'];
                    if ($name == 'id')
                        continue;
                    if ($f['type'] == 'uuid') {
                        if ($data[$name] == '') {
                            $data[$name] = DCS_EMPTY_ENTITY;
                        }
                    } elseif ($f['type'] == 'boolean') {
                        if ($data[$name] == 't') {
                            $data[$name] = 'true';
                        } elseif ($data[$name] == 'true') {
                            $data[$name] = 'true';
                        } else {
                            $data[$name] = 'false';
                        }
                    }
                    $fname .= ", $name";
                    $arval .= ", '$data[$name]'";
                }
                $fname = substr($fname, 1);
                $arval = substr($arval, 1);
                $sql = "INSERT INTO \"$arow[dbtable]\" ($fname) VALUES ($arval) RETURNING \"id\"";
                $res = self::dm_query($sql);
                $obj = $res->fetch(PDO::FETCH_ASSOC);
                $ares = array('status' => 'OK', 'id' => $obj['id']);
            } else {
                $ares = array('status' => 'ERROR', 'msg' => $err);
            }
        }
        return $ares;
    }

    public static function CollectionItemDelete($id) {
        $row = self::getCollectionItemByID($id);
        $res = '';
        $arow = $row->fetch(PDO::FETCH_ASSOC);
        $sql = "DELETE FROM \"$arow[dbtable]\" WHERE id=:id";
        $res = self::dm_query($sql, array('id' => $id));
        $ares = array('status' => 'OK', 'id' => $arow['id']);
        return $ares;
    }

    public static function getSettings() {
        $sql = "select ct.id, pv_set.value as id_settings, ct_set.name as name_settings, pv_prop.value as propid, pv_val.value as value, ct_type.name as type from \"CTable\" as ct "
                . "inner join \"MDTable\" as md "
                . "on ct.mdid=md.id "
                . "inner join \"CPropValue_cid\" as pv_usr "
                . "inner join \"CProperties\" as cp_usr "
                . "on pv_usr.pid = cp_usr.id "
                . "and cp_usr.name = 'user' "
                . "on ct.id=pv_usr.id "
                . "inner join \"CPropValue_cid\" as pv_set "
                . "inner join \"CProperties\" as cp_set "
                . "on pv_set.pid = cp_set.id "
                . "and cp_set.name = 'settings' "
                . "inner join \"CTable\" as ct_set "
                . "on pv_set.value = ct_set.id "
                . "inner join \"CPropValue_cid\" as pv_prop "
                . "inner join \"CProperties\" as cp_prop "
                . "on pv_prop.pid = cp_prop.id "
                . "and cp_prop.name = 'propstemplate' "
                . "inner join \"CPropValue_cid\" as pv_type "
                . "inner join \"CProperties\" as cp_type "
                . "on pv_type.pid = cp_type.id "
                . "and cp_type.name = 'type' "
                . "inner join \"CTable\" as ct_type "
                . "on pv_type.value = ct_type.id "
                . "on pv_prop.value = pv_type.id "
                . "on pv_set.value = pv_prop.id "
                . "on ct.id=pv_set.id "
                . "inner join \"CPropValue_str\" as pv_val "
                . "inner join \"CProperties\" as cp_val "
                . "on pv_val.pid = cp_val.id "
                . "and cp_val.name = 'value' "
                . "on ct.id=pv_val.id "
                . "where md.name='user_settings' and pv_usr.value = :userid";

        $params = array();
        $params['userid'] = $_SESSION['user_id'];
        $res = self::dm_query($sql, $params);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDefaultValue($plist) {
        $objs = array();
        $objs[DCS_EMPTY_ENTITY] = array();
        $settings = self::getSettings();
        foreach ($plist as $prop) {
            if ($prop['isedate']) {
                $objs[DCS_EMPTY_ENTITY][$prop['id']] = array('name' => date("Y-m-d"), 'id' => '');
            } else {
                if ((strtolower($prop['name_propid']) == 'user') || (strtolower($prop['name_propid']) == 'head')) {
                    $user = CollectionSet::getCDetails($_SESSION['user_id']);
                    $objs[DCS_EMPTY_ENTITY][$prop['id']] = array('name' => $user['synonym'], 'id' => $_SESSION['user_id']);
                }
// отказ от присваивания номера при создании формы в пользу присваивания номера при записи нового                
//                elseif (strtolower ($prop['name_propid'])=='number')
//                {
//                    $number = self::getNumber($prop['id'])+1;
//                    $objs[DCS_EMPTY_ENTITY][$prop['id']]= array('name'=>$number,'id'=>'');
//                }    
                else {
                    $key = array_search($prop['propid'], array_column($settings, 'propid'));
                    if ($key !== false) {
                        if ($settings[$key]['type'] == 'id') {
                            $valid = $settings[$key]['value'];
                            $obj = new Entity($valid);
                            $valname = $obj->getname();
                        } elseif ($settings[$key]['type'] == 'cid') {
                            $valid = $settings[$key]['value'];
                            $obj = new CollectionItem($valid);
                            $valname = $obj->getsynonym();
                        } else {
                            $valname = $settings[$key]['value'];
                            $valid = '';
                        }
                        $objs[DCS_EMPTY_ENTITY][$prop['id']] = array('name' => $valname, 'id' => $valid);
                    }
                }
            }
        }
        return $objs;
    }

    public static function getNumber($propid) {
        $ttbl = array();
        $sql = "SELECT pv.value as number, it.id, it.dateupdate, it.entityid FROM \"PropValue_int\" as pv INNER JOIN \"IDTable\" as it INNER JOIN \"MDProperties\" as mp ON it.propid=mp.id ON pv.id=it.id WHERE mp.id=:propid";
        $ttbl[] = self::createtemptable($sql, 'a1', array('propid' => $propid));
        $sql = "SELECT max(dateupdate) as dateupdate, entityid FROM a1 GROUP BY entityid";
        $ttbl[] = self::createtemptable($sql, 'a2');
        $sql = "SELECT max(number) as number FROM a1 inner join a2 ON a1.entityid=a2.entityid AND a1.dateupdate=a2.dateupdate";
        $res = self::dm_query($sql);
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $res = $row['number'];
        } else {
            $res = 0;
        }
        self::droptemptable($ttbl);
        return $res;
    }

    public static function get_md_access_text($action = '') {
        if (($action == 'EDIT') || ($action == 'SET_EDIT') || ($action == 'CREATE')) {
            $dop = self::get_access_text('md', 'write');
        } else {
            $dop = self::get_access_text('md', 'read');
        }
        return $dop;
    }

    public static function get_col_access_text($ttname) {
        return self::get_access_text($ttname, 'read', 'AccessRightCom', 'cid');
    }

    public static function get_access_text($ttname, $mode = 'read', $ra_tbl = 'RoleAccess', $type = 'mdid') {
        $dop = '';
        if (!User::isAdmin()) {
            $dop = "$ttname.id in (SELECT pv.value FROM \"CPropValue_$type\" as pv 
		inner join \"CTable\" as ct
			inner join \"MDTable\" as md_ra
			on ct.mdid = md_ra.id
			and md_ra.name='" . $ra_tbl . "'
			inner join \"CPropValue_cid\" as pv_rol
				inner join \"CProperties\" as cp_rol
				on pv_rol.pid=cp_rol.id
				and cp_rol.name='role_kind'
				inner join \"CPropValue_cid\" as pv_usrol
					inner join \"CProperties\" as cp_usrol
					on pv_usrol.pid=cp_usrol.id
					and cp_usrol.name='role'
					inner join \"CPropValue_cid\" as pv_usr
						inner join \"CProperties\" as cp_usr
						on pv_usr.pid=cp_usr.id
						and cp_usr.name='user'
					on pv_usrol.id=pv_usr.id
				on pv_rol.value=pv_usrol.value
				and pv_rol.id<>pv_usrol.id
			on ct.id = pv_rol.id
                        inner join \"CPropValue_bool\" as ct_rd
				inner join \"CProperties\" as cp_rd
				on ct_rd.pid=cp_rd.id
				and cp_rd.name= '" . $mode . "'
			on ct.id = ct_rd.id
			AND ct_rd.value 
		on pv.id=ct.id
                where pv_usr.value = :userid)";
        }
        return $dop;
    }
    public static function getTT_entity($ttname, $mdid, $propid, $val, $type, $oper)
    {
        $ar_tt0 = array();
        $params = array();
        $params['mdid'] = $mdid;
        $params['propid'] = $propid;
        $sql = "SELECT it.dateupdate, it.entityid, it.propid, it.id FROM \"IDTable\" as it "
                . "inner join \"ETable\" as et "
                . "on it.entityid=et.id "
                . "and et.mdid = :mdid "
                . "inner join \"MDProperties\" as pt "
                . "on it.propid=pt.id "
                . "and pt.propid=:propid";
        $ar_tt0[] = self::createtemptable($sql, 'tt_per0', $params);

        $sql = "SELECT max(dateupdate) AS dateupdate, entityid, propid  FROM tt_per0 "
                . "GROUP BY entityid, propid";
        $ar_tt0[] = self::createtemptable($sql, 'tt_it0');

        $sql = "SELECT tper.entityid, tper.propid, tper.id as verid FROM tt_per0 AS tper "
                . "INNER JOIN tt_it0 as tid "
                . "ON tper.entityid=tid.entityid "
                . "AND tper.propid=tid.propid "
                . "AND tper.dateupdate=tid.dateupdate";
        $ar_tt0[] = self::createtemptable($sql, 'tt_sel0');

        $sql = "SELECT ts.entityid, ts.entityid as id, ts.propid, pv.value FROM tt_sel0 AS ts 
                        INNER JOIN \"PropValue_$type\" AS pv
                        ON ts.verid = pv.id where pv.value$oper:val";
        $params = array();
        $params['val'] = $val;
        $res = self::createtemptable($sql, $ttname, $params);
        self::droptemptable($ar_tt0);
        return $res;
    }

    public static function getTT_from_ttent(
            $ttname, 
            $tt_ent, 
            $propid, 
            $type, 
            $tt_val = '', 
            $existonly = TRUE) 
    {
        $ar_tt0 = array();
        $params = array();
        $params['propid'] = $propid;
        $sql = "SELECT DISTINCT it.entityid, it.propid FROM \"IDTable\" as it "
                . "inner join $tt_ent as et "
                . "on it.entityid=et.id "
                . "inner join \"MDProperties\" as pt "
                . "on it.propid=pt.id "
                . "and pt.propid=:propid";
        $ar_tt0[] = DataManager::createtemptable($sql, 'tt_per0', $params);

        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid FROM \"IDTable\" as it INNER JOIN tt_per0 AS et ON it.entityid=et.entityid AND it.propid=et.propid
                      GROUP BY it.entityid, it.propid";
        $ar_tt0[] = DataManager::createtemptable($sql, 'tt_it0');

        $sqlfilt = '';
        if ($tt_val != '') {
            $sqlfilt = " inner join $tt_val AS ch on pv.value=ch.id ";
        }
        if ($existonly) {
            $sql = "SELECT tper.entityid, tper.propid, pv.value, it.dateupdate FROM tt_it0 AS tper 
                            INNER JOIN \"IDTable\" as it
                                INNER JOIN \"PropValue_$type\" AS pv$sqlfilt
                                ON it.id = pv.id
                            ON tper.entityid = it.entityid
                            AND tper.propid=it.propid
                            AND tper.dateupdate=it.dateupdate";
        } else {
            $sql = "SELECT tper.entityid, tper.propid, pv.value, it.dateupdate FROM tt_it0 AS tper 
                            LEFT JOIN \"IDTable\" as it
                                INNER JOIN \"PropValue_$type\" AS pv$sqlfilt
                                ON it.id = pv.id
                            ON tper.entityid = it.entityid
                            AND tper.propid=it.propid
                            AND tper.dateupdate=it.dateupdate";
        }
        $res = DataManager::createtemptable($sql, $ttname);

        DataManager::droptemptable($ar_tt0);
        return $res;
    }

    public static function getTT_from_ttent_prop($ttname, $tt_ent, $propid, $type, $tt_val = '') {
        $ar_tt0 = array();
        $params = array();
        $params['propid'] = $propid;
        $sql = "SELECT it.entityid, it.propid FROM \"IDTable\" as it "
                . "inner join $tt_ent as et "
                . "on it.entityid=et.entityid "
                . "inner join \"MDProperties\" as pt "
                . "on it.propid=pt.id "
                . "and pt.id=:propid";

        $ar_tt0[] = DataManager::createtemptable($sql, 'tt_per0', $params);

        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid FROM \"IDTable\" as it INNER JOIN tt_per0 AS et ON it.entityid=et.entityid AND it.propid=et.propid
                      GROUP BY it.entityid, it.propid";
        $ar_tt0[] = DataManager::createtemptable($sql, 'tt_it0');

        if ($tt_val == '') {
            $sql = "SELECT tper.entityid, tper.entityid as id, tper.propid, pv.value FROM tt_it0 AS tper 
                            INNER JOIN \"IDTable\" as it
                                INNER JOIN \"PropValue_$type\" AS pv
                                ON it.id = pv.id
                            ON tper.entityid = it.entityid
                            AND tper.propid=it.propid
                            AND tper.dateupdate=it.dateupdate";
        } else {
            $sql = "SELECT tper.entityid, tper.entityid as id, tper.propid, pv.value FROM tt_it0 AS tper 
                            INNER JOIN \"IDTable\" as it
                                INNER JOIN \"PropValue_$type\" AS pv
                                    inner join $tt_val AS ch
                                    on pv.value=ch.id
                                ON it.id = pv.id
                            ON tper.entityid = it.entityid
                            AND tper.propid=it.propid
                            AND tper.dateupdate=it.dateupdate";
        }

        $res = DataManager::createtemptable($sql, $ttname);
        DataManager::droptemptable($ar_tt0);
        return $res;
    }

    public static function getTT_from_ttprop($ttname, $prop_ent, $type, $tt_val) {
        $ar_tt0 = array();
        $params = array();
        $sql = "SELECT it.entityid, it.propid FROM \"IDTable\" as it 
                      inner join \"PropValue_$type\" as pv 
                        inner join $tt_val as el0
                        on pv.value=el0.entityid
                      on it.id=pv.id 
                      where it.propid = :propid";
        $params['propid'] = $prop_ent;
        $ar_tt0[] = self::createtemptable($sql, 'tt_per0', $params);

        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid FROM \"IDTable\" as it INNER JOIN tt_per0 AS et ON it.entityid=et.entityid AND it.propid=et.propid
                      GROUP BY it.entityid, it.propid";
        $ar_tt0[] = self::createtemptable($sql, 'tt_it0');

        if ($tt_val == '') {
            $sql = "SELECT tper.entityid, tper.propid, pv.value FROM tt_it0 AS tper 
                        INNER JOIN \"IDTable\" as it
                            INNER JOIN \"PropValue_$type\" AS pv
                            ON it.id = pv.id
                        ON tper.entityid = it.entityid
                        AND tper.propid=it.propid
                        AND tper.dateupdate=it.dateupdate";
        } else {
            $sql = "SELECT tper.entityid, tper.propid, pv.value FROM tt_it0 AS tper 
                        INNER JOIN \"IDTable\" as it
                            INNER JOIN \"PropValue_$type\" AS pv
                                inner join $tt_val AS ch
                                on pv.value=ch.entityid
                            ON it.id = pv.id
                        ON tper.entityid = it.entityid
                        AND tper.propid=it.propid
                        AND tper.dateupdate=it.dateupdate";
        }

        $res = self::createtemptable($sql, $ttname);
        self::droptemptable($ar_tt0);
        return $res;
    }

    public static function getInterfaceContents($interfaceid) {
        $sql = "select ct_intcont.name as name, ct_intcont.synonym as synonym,  pv_intcont.value as id, pv_rank.value as rank from \"CPropValue_str\" as pv_intcont
                inner join \"CProperties\" as cp_intcont
                on pv_intcont.pid=cp_intcont.id
                and cp_intcont.name='object'
                inner join \"CPropValue_cid\" as pv_int
                        inner join \"CProperties\" as cp_int
                        on pv_int.pid=cp_int.id
                        and cp_int.name='interface'
                on pv_intcont.id=pv_int.id
                inner join \"CPropValue_int\" as pv_rank
                        inner join \"CProperties\" as cp_rank
                        on pv_rank.pid=cp_rank.id
                        and cp_rank.name='rank'
                on pv_intcont.id=pv_rank.id
                inner join \"CTable\" as ct_intcont
                on pv_intcont.id = ct_intcont.id
                where pv_int.value = :interfaceid order by pv_rank.value";
        $res = self::dm_query($sql, array('interfaceid' => $interfaceid));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_access_group($userid = '') {
        if ($userid == '') {
            $userid = $_SESSION['user_id'];
        }
        $sql = "select pv_group.value as user_group from \"CTable\" as ct
                    inner join \"MDTable\" as mt
                    on ct.mdid = mt.id
                    and mt.name='usergroup'
                    inner join \"CPropValue_cid\" as pv_group
                            inner join \"CProperties\" as cp_group
                            on pv_group.pid=cp_group.id
                            and cp_group.name='group'
                    on ct.id=pv_group.id
                    inner join \"CPropValue_cid\" as pv_usr
                            inner join \"CProperties\" as cp_usr
                            on pv_usr.pid=cp_usr.id
                            and cp_usr.name='user'
                    on ct.id=pv_usr.id
                    where pv_usr.value = :userid";
        $res = self::dm_query($sql, array('userid' => $userid));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_related_fields($propid) {

        $sql = "select pv_lead.value as lead, pv_dep.value as depend from \"CTable\" as ct
                    inner join \"MDTable\" as mt
                    on ct.mdid = mt.id
                    and mt.name='RelatedFields'
                    inner join \"CPropValue_cid\" as pv_lead
                            inner join \"CProperties\" as cp_lead
                            on pv_lead.pid=cp_lead.id
                            and cp_lead.name='prop_lead'
                    on ct.id=pv_lead.id
                    and pv_lead.value = :propid
                    inner join \"CPropValue_cid\" as pv_dep
                            inner join \"CProperties\" as cp_dep
                            on pv_dep.pid=cp_dep.id
                            and cp_dep.name='prop_depend'
                    on ct.id=pv_dep.id";
        $res = self::dm_query($sql, array('propid' => $propid));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_event_trigger($eventname, $mdid, $propid) {
        $sql = "select ct.id, ct.name, ct.synonym from \"CTable\" as ct
            inner join \"MDTable\" as mt
            on ct.mdid = mt.id
            and mt.name='Trigs'
            inner join \"CPropValue_cid\" as pv_event
                inner join \"CProperties\" as cp_event
                on pv_event.pid = cp_event.id
                and cp_event.name='event'
                inner join \"CTable\" as ct_event
                on pv_event.value = ct_event.id
                and ct_event.name=:eventname
            on ct.id = pv_event.id
            inner join \"CPropValue_mdid\" as pv_object
                inner join \"CProperties\" as cp_object
                on pv_object.pid = cp_object.id
                and cp_object.name='object'
            on ct.id = pv_object.id
            and pv_object.value=:mdid
            inner join \"CPropValue_cid\" as pv_prop
                inner join \"CProperties\" as cp_prop
                on pv_prop.pid = cp_prop.id
                and cp_prop.name='property_template'
            on ct.id = pv_prop.id
            and pv_prop.value=:propid";
        $params = array('eventname' => $eventname, 'mdid' => $mdid, 'propid' => $propid);
        $res = self::dm_query($sql, $params);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    
    //возвращает массив ид сущностей которые содержат в ТЧ указанную строку
    //itemid - ид строки ТЧ
    public static function get_obj_by_item($itemid)
    {
        $sql = "select it.entityid as id from \"IDTable\" as it
                    inner join \"PropValue_id\" as pv
                        inner join \"SetDepList\" as sdl
                        on pv.value = sdl.parentid
                        and sdl.childid = :itemid
                    on it.id=pv.id";
        $params = array();
        $params['itemid'] = $itemid;
        
        $res = self::dm_query($sql,$params);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }        
    //возвращает массив объектов метаданных которые содержат в ТЧ метаданные указанной строки 
    //mdid - ид метаданных строки ТЧ
    public static function get_parentmd_by_item($mdid)
    {        
        $sql = "select mi.name as mditem, mdp.name as mdname, mdp.id as mdid, mp.id as pid, mp.propid, pv.value as setmdid from \"MDTable\" as mdp
                    inner join \"CTable\" as mi
                    on mdp.mditem = mi.id
                    inner join \"MDProperties\" as mp 
                        inner join \"CTable\" as ct
                            inner join \"MDTable\" as md
                            on ct.mdid=md.id
                            inner join \"CProperties\" as cp
                                inner join \"CPropValue_mdid\" as pv
                                on cp.id=pv.pid
                                and pv.value in (
                                    select mdp.id from \"MDTable\" as mdp
                                        inner join \"CTable\" as mi
                                        on mdp.mditem = mi.id
                                        and mi.name='Sets'
                                        inner join \"MDProperties\" as mp 
                                            inner join \"CTable\" as ct
                                                inner join \"MDTable\" as md
                                                on ct.mdid=md.id
                                                inner join \"CProperties\" as cp
                                                        inner join \"CPropValue_mdid\" as pv
                                                        on cp.id=pv.pid
                                                        and pv.value=:mdid
                                                on ct.mdid=cp.mdid
                                                and cp.name='valmdid'
                                                and pv.id=ct.id
                                            and md.name = 'PropsTemplate'
                                            on mp.propid = ct.id
                                        on mp.mdid=mdp.id
                                    )
                            on ct.mdid=cp.mdid
                            and cp.name='valmdid'
                            and pv.id=ct.id
                        and md.name = 'PropsTemplate'
                        on mp.propid = ct.id
                    on mp.mdid=mdp.id";
        $params = array();
        $params['mdid'] = $mdid;
        $res = self::dm_query($sql,$params);
        $ar_obj = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $ar_obj[$row['mdid']] = $row;
        }
        return $ar_obj;
    }    
    public static function get_access_prop()
    {
        $userid=$_SESSION['user_id'];
        $sql = "select pv_group.value as user_group, pv_prop.value as propid, 
                ct_prop.name as propname, pt.name as name_type, pv_val.value as value, 
                pv_rd.value as rd, pv_wr.value as wr from \"CTable\" as ct
                    inner join \"MDTable\" as mt
                    on ct.mdid = mt.id
                    and mt.name='access_rights'
                    inner join \"CPropValue_cid\" as pv_group
                        inner join \"CProperties\" as cp_group
                        on pv_group.pid=cp_group.id
                        and cp_group.name='user_group'
                        inner join \"CTable\" as ct_gr
                            inner join \"MDTable\" as mt_gr
                            on ct_gr.mdid = mt_gr.id
                            and mt_gr.name='usergroup'
                            inner join \"CPropValue_cid\" as pv_grp
                                    inner join \"CProperties\" as cp_grp
                                    on pv_grp.pid=cp_grp.id
                                    and cp_grp.name='group'
                            on ct_gr.id=pv_grp.id
                            inner join \"CPropValue_cid\" as pv_usr
                                    inner join \"CProperties\" as cp_usr
                                    on pv_usr.pid=cp_usr.id
                                    and cp_usr.name='user'
                            on ct_gr.id=pv_usr.id
                            and pv_usr.value = :userid
                        on pv_group.value = pv_grp.value
                    on ct.id=pv_group.id
                    left join \"CPropValue_cid\" as pv_prop
                            inner join \"CProperties\" as cp_prop
                            on pv_prop.pid=cp_prop.id
                            and cp_prop.name='prop_template'
                            inner join \"CPropValue_cid\" as pst
                                INNER JOIN \"CProperties\" as cprs
                                ON pst.pid = cprs.id
                                AND cprs.name='type'
                                INNER JOIN \"CTable\" as pt
                                ON pst.value = pt.id
                            on pv_prop.value = pst.id
                            inner join \"CTable\" as ct_prop
                            on pv_prop.value = ct_prop.id
                    on ct.id=pv_prop.id
                    left join \"CPropValue_str\" as pv_val
                            inner join \"CProperties\" as cp_val
                            on pv_val.pid=cp_val.id
                            and cp_val.name='value'
                    on ct.id=pv_val.id
                    left join \"CPropValue_bool\" as pv_rd
                            inner join \"CProperties\" as cp_rd
                            on pv_rd.pid=cp_rd.id
                            and cp_rd.name='read'
                    on ct.id=pv_rd.id
                    left join \"CPropValue_bool\" as pv_wr
                            inner join \"CProperties\" as cp_wr
                            on pv_wr.pid=cp_wr.id
                            and cp_wr.name='write'
                    on ct.id=pv_wr.id";
	$res = self::dm_query($sql, array('userid'=>$userid));	
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function arr_rls($propid, $access_prop,$action)
    {
        $rls = array();
        foreach ($access_prop as $prop)
        {
            if ($prop['propid'] == $propid)
            {
                if (($action === 'EDIT')||
                    ($action === 'SET_EDIT')||
                    ($action === 'CREATE')||
                    ($action === 'CREATE_PROPERTY')) {
                    if ($prop['wr'] === true)
                    {    
                        $rls[] = $prop['value'];
                    }    
                }    
                else 
                {
                    if (($prop['rd'] === true)||($prop['wr'] === true))
                    {    
                        $rls[] = $prop['value'];
                    }    
                }
            }    
        }    
        return $rls;
    }

}
