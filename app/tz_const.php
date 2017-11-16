<?php
define("TZ_DBNAME","tezdb");
define("TZ_DBUSER","postgres");
define("TZ_DBPASS","3141592");
define("TZ_COMPANY_SHORTNAME","ТЭЗ");
define("TZ_COMPANY_NAME","НПП ТЭЗ");
define("TZ_COMPANY_FULLNAME","Томилинский электронный завод");
define("TZ_COUNT_REC_BY_PAGE", 100);
define("TZ_EDIT_DELAY", '00:20');
define("TZ_EMPTY_ENTITY", '00000000-0000-0000-0000-000000000000');
define("TZ_EMPTY_DATE",date("Y-m-d H:i:s",mktime(0,0,0,1,1,1)));
define("TZ_EMPTY_TIME",mktime(0,0,0,1,1,1));
define("TZ_TYPE_SET",2);
define("TZ_TYPE_ITEM",3);
define("TZ_TYPE_REF",1);
define("TZ_TYPE_DOC",4);
define("TZ_TYPE_VAL",5);
define("TZ_TYPE_EMPTY",0);
define("TZ_ONE_TO_ONE",0);
define("TZ_ONE_TO_MANY",1);
define("TZ_MANY_TO_MANY",2);
define("TZ_MANY_TO_ONE",3);
define("TZ_EMPTY",-1);
define("TZ_MUSTBE_PROPERTY",1);
define("TZ_PROPS_RANK_ID",18);
define("TZ_PROPS_NAME_ID",1);
define("TZ_PROPS_ACTIVITY_ID",7);
define("TZ_PROPS_DATE_ID",8);
define("TZ_PROPS_NUMBER_ID",57);
define("TZ_CS_PROPS_ID",29);
define("IT_IS_UUID","^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$");
define("TZ_UPLOAD_DIR","/upload");
define("TZ_UPLOAD_LOG","/log/upload");
define("TZ_UPLOAD_IMPORT_DIR","/upload/import");
define("TZ_UPLOAD_IMPORT_LOG","/log/import");


