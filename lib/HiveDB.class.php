<?php
class HiveDB {

    var $db;

    public function __construct() {       
        $this->db = get_db('hive'); ;
    }
	
	function get_all_tables(){
		return $this->db->get_list_h("select TBL_ID,NAME,TBL_NAME,TBL_TYPE,LOCATION,PARAM_KEY,PARAM_VALUE from TBLS join DBS on DBS.DB_ID=TBLS.DB_ID join SDS on TBLS.TBL_ID =SDS.CD_ID left join SERDE_PARAMS on SERDE_PARAMS.SERDE_ID=TBLS.SD_ID and SERDE_PARAMS.PARAM_KEY='field.delim' order by TBL_ID desc",true);
	}

	function get_tables($d,$t){
		$dbname=$d?"NAME='".$d."'":"1=1";
		$tblname=$t?"TBL_NAME='".$t."'":"1=1";

		return $this->db->get_list_h("select TBLS.TBL_ID,NAME,TBL_NAME,TBL_TYPE,LOCATION,PARAM_KEY,PARAM_VALUE from TBLS join DBS on DBS.DB_ID=TBLS.DB_ID join SDS on TBLS.TBL_ID =SDS.CD_ID left join SERDE_PARAMS on SERDE_PARAMS.SERDE_ID=TBLS.SD_ID and SERDE_PARAMS.PARAM_KEY='field.delim'  where $dbname and $tblname order by TBL_ID desc",true);
	}

	function get_table_fields($d,$t){
		$dbname=$d?"NAME='".$d."'":"1=1";
		$tblname=$t?"TBL_NAME='".$t."'":"1=1";

		return $this->db->get_list_h("select TBL_ID,TBL_NAME,TBL_TYPE,COLUMN_NAME,TYPE_NAME,INTEGER_IDX from TBLS join DBS on DBS.DB_ID=TBLS.DB_ID join COLUMNS_V2 on TBLS.TBL_ID =COLUMNS_V2.CD_ID  where $dbname and $tblname order by TBL_ID desc,INTEGER_IDX",true);
	}
	
	
	
	
	
}