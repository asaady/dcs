<?php
namespace tzVendor;
use PDO;

class ApiAcceptOtk
{
    function getdata($sdate='')
    {
        Common_data::_log(TZ_API_LOG,'activate API acceptOTK with dateupdate = '.$sdate);
        $data=array();
        $doc_mdid='5fe64ab0-4d6e-4f09-9ec3-eef265172a07'; // mdid приемка отк документ
        $item_mdid = 'c00c0809-56f5-4ad4-b7bb-2b8ecaa126db'; //mdid строка ТЧ док ПриемкаОТК
        $propid_izv='45bef4ce-ec70-4b11-8731-21c880d1f671'; //реквизит [номер извещения] строки ТЧ ПриемкаОТК
        $propid_set='2d6a4789-d534-402a-aef6-d052c10d700f'; //ТЧ [Данные] док ПриемкаОТК
        if (trim($sdate) == '') 
        {
            $data['STATUS']="ERROR";
            $data['MESSAGE']="Parameter [date] is empty";
            Common_data::_log(TZ_API_LOG,$data['MESSAGE']);
            return $data;
        }        
        if (($timestamp = strtotime($sdate)) === false) 
        {
            $data['STATUS']="ERROR";
            $data['MESSAGE']="Parameter [date] is bad data";
            Common_data::_log(TZ_API_LOG,$data['MESSAGE']);
            return $data;
        }
        $att=array();
        $sql = "select it.id, it.entityid, pv.value as izv, it.dateupdate from \"IDTable\" as it inner join \"PropValue_str\" as pv on it.id=pv.id where it.propid=:propid and it.dateupdate>=:dateupdate";
        $params=array('propid'=>$propid_izv,'dateupdate'=>$sdate);
        $att[]= DataManager::createtemptable($sql,'tt_api_et',$params);   
        
        $sql = "select et.entityid, max(et.dateupdate) as dateupdate from tt_api_et as et group by et.entityid";
        $att[]= DataManager::createtemptable($sql,'tt_api_lt');   

        $sql = "select et.entityid, et.izv from tt_api_et as et inner join tt_api_lt as lt on et.entityid=lt.entityid and et.dateupdate=lt.dateupdate";
        $att[]= DataManager::createtemptable($sql,'tt_api_it');   

        $sql = "select sdl.parentid as setid, sdl.childid as itemid from \"SetDepList\" as sdl inner join tt_api_it as it on sdl.childid=it.entityid";
        $att[]= DataManager::createtemptable($sql,'tt_api_sdt');   
        
        $sql = "select distinct sdl.setid from tt_api_sdt as sdl";
        $att[]= DataManager::createtemptable($sql,'tt_api_st');   
        
        
        $sql = "select it.entityid as docid, pv.value as setid from \"IDTable\" as it inner join \"PropValue_id\" as pv inner join tt_api_st as st on pv.value = st.setid on it.id=pv.id where it.propid=:propid";
        $params=array('propid'=>$propid_set);
        $att[]= DataManager::createtemptable($sql,'tt_api_dst',$params);   

        $sql = "select distinct it.docid from tt_api_dst as it";
        $att[]= DataManager::createtemptable($sql,'tt_api_dt');   

        $sql = "select * from tt_api_sdt";
        $sth = DataManager::dm_query($sql);
        $sdt = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        
        $sql = "select * from tt_api_dt";
        $res = DataManager::dm_query($sql);
        $doc_plist = MdpropertySet::getMDProperties($doc_mdid, 'API', " WHERE mp.mdid = :mdid ");
        $item_plist = MdpropertySet::getMDProperties($item_mdid, 'API', " WHERE mp.mdid = :mdid ");
        
        Common_data::_log(TZ_API_LOG,' count api request items = '.count($sdt));
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $doc = new Entity($row['docid'],'API');
            if (!$doc->getactivity())
            {
                //помеченные на удаление документы не обрабатываем
                continue;
            }    
            $docid = $doc->getid();
            $doc_data = $doc->getdata();
            Common_data::_log(TZ_API_LOG,' docid = '.$docid);
            $setid = $doc_data[$propid_set]['id'];
            foreach ($sdt as $si)
            {
                //ищем строки текущего документа
                if ($si['setid']!=$setid)
                {
                    continue;
                }    
                $itemid = $si['itemid'];
                $item = new Entity($itemid,'API');
                $itemdata = $item->getdata();
                Common_data::_log(TZ_API_LOG,'   itemid = '.$itemid);
                $objs=array();
                foreach ($doc_plist as $prop)
                {
                    $propname = str_replace(' ','',$prop['name']);
                    $propname = str_replace('/','',$propname);
                    $propname = str_replace('.','',$propname);
                    if ($prop['valmdtypename']=='Sets')
                    {
                        foreach ($item_plist as $iprop)
                        {
                            $ipropname = str_replace(' ','',$iprop['name']);
                            $ipropname = str_replace('/','',$ipropname);
                            $ipropname = str_replace('.','',$ipropname);
                            if (($iprop['type']=='id')||($iprop['type']=='cid')||($iprop['type']=='mdid'))
                            {    
                                $objs['item_'.$ipropname]=$itemdata[$iprop['id']];
                            }
                            else 
                            {
                                if ($ipropname=='id')
                                {
                                    $objs['item_'.$ipropname]=$itemid;
                                }   
                                else
                                {
                                    $objs['item_'.$ipropname]=$itemdata[$iprop['id']]['name'];
                                }    
                            }
                        }    
                    }   
                    else 
                    {
                        if (($prop['type']=='id')||($prop['type']=='cid')||($prop['type']=='mdid'))
                        {
                            $objs['doc_'.$propname]=$doc_data[$prop['id']];
                        }   
                        else
                        {
                            if ($propname=='id')
                            {
                                $objs['doc_'.$propname]=$docid;
                            }   
                            else
                            {
                                $objs['doc_'.$propname]=$doc_data[$prop['id']]['name'];
                            }    
                        }    
                    }
                }    
                $data[]=$objs;
            }    
        }        
	DataManager::droptemptable($att);
	return $data;
        
    }
}

