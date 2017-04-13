<?php
function escapeSqlParam(&$text){
    $text = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($text) : mysql_escape_string($text);
    $text = "'" . $text . "'";
}
function escapeSqlParam2($text){
    $text = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($text) : mysql_escape_string($text);
    return "'" . $text . "'";
}

function get_private_db($db_name){
    $db_host = "localhost";
    $db_user = "wuser";
    $db_pass = "sichang0923";
    return new MysqlDAO($db_host, $db_user, $db_pass, $db_name);     
}


class MysqlDAO {
    private $db_host; //数据库主机
    private $db_user; //数据库用户名
    private $db_pwd; //数据库用户名密码
    private $db_database; //数据库名
    private $conn; //数据库连接标识;
    private $result; //执行query命令的结果资源标识
    public $sql; //sql执行语句
    private $row; //返回的条目数
    private $coding; //数据库编码，GBK,UTF8,gb2312
    public $last_error = false;
    private $bulletin = true; //是否开启错误记录
    private $show_error = true; //测试阶段，显示所有错误,具有安全隐患,默认关闭
    private $is_error = false; //发现错误是否立即终止,默认true,建议不启用，因为当有问题时用户什么也看不到是很苦恼的

    /*构造函数*/
    public function __construct($db_host, $db_user, $db_pwd, $db_database, $conn = "conn", $coding = "UTF8") {
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pwd = $db_pwd;
        $this->db_database = $db_database;
        $this->conn = $conn;
        $this->coding = $coding;
        $this->connect();
    }

    /*数据库连接*/
    public function connect() {
        if ($this->conn == "pconn") {
            //永久链接
            $this->conn = mysql_pconnect($this->db_host, $this->db_user, $this->db_pwd);
        } else {
            //即使链接
            $this->conn = mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
        }

        if (!mysql_select_db($this->db_database, $this->conn)) {
            if ($this->show_error) {
                $this->show_error("数据库不可用：", $this->db_database);
            }
        }
        mysql_query("SET NAMES $this->coding");
    }

    public function escape($value) {
        //if ($this->conn) {
            return mysql_real_escape_string($value);
        //}
    }

    /*数据库执行语句，可执行查询添加修改删除等任何sql语句*/
    public function query2($sql) {
        if ($sql == "") {
            $this->show_error("SQL语句错误：", "SQL查询语句为空");
        }
        $this->sql = $sql;
        
        //force reconnect
        mysql_close($this->conn);
        $this->conn = null;
        $this->connect();

        $result = mysql_query($this->sql, $this->conn);

        if (!$result) {
            //调试中使用，sql语句出错时会自动打印出来
            if ($this->show_error) {
                $this->show_error("Error：", $this->sql);
            }
            $this->last_error = true;
        } else {
            $this->result = $result;
        }
        return $this->result;
    }
    
    public function query($sql) {
        if ($sql == "") {
            return false;
        }
        $this->sql = $sql;
        
        //force reconnect
        mysql_close($this->conn);
        $this->conn = null;
        $this->connect();

        $result = mysql_query($this->sql, $this->conn);

        if (!$result) {
            //调试中使用，sql语句出错时会自动打印出来
            if ($this->show_error) {
                $this->result = false;
            }
            $this->last_error = true;
        } else {
            $this->result = $result;
        }
        return $this->result;
    }

    /*创建添加新的数据库*/
    public function create_database($database_name) {
        $database = $database_name;
        $sqlDatabase = 'create database ' . $database;
        $this->query($sqlDatabase);
    }

    /*查询服务器所有数据库*/
    //将系统数据库与用户数据库分开，更直观的显示？
    public function show_databases() {
        $this->query("show databases");
        echo "现有数据库：" . $amount = $this->db_num_rows($rs);
        echo "<br />";
        $i = 1;
        while ($row = $this->fetch_array($rs)) {
            echo "$i $row[Database]";
            echo "<br />";
            $i++;
        }
    }

    //以数组形式返回主机中所有数据库名
    public function databases() {
        $rsPtr = mysql_list_dbs($this->conn);
        $i = 0;
        $cnt = mysql_num_rows($rsPtr);
        while ($i < $cnt) {
            $rs[] = mysql_db_name($rsPtr, $i);
            $i++;
        }
        return $rs;
    }

    /*查询数据库下所有的表*/
    public function show_tables($database_name) {
        $this->query("show tables");
        echo "现有数据库：" . $amount = $this->db_num_rows($rs);
        $i = 1;
        while ($row = $this->fetch_array($rs)) {
            $columnName = "Tables_in_" . $database_name;
            echo "$i $row[$columnName]";
            $i++;
        }
    }

    public function get_list($sql) {
        $result = $this->query($sql);
        $return_data = array ();
        while ($row = $this->fetch_array($result)) {
            $return_data[] = $row;
        }
        return $return_data;
    }
    
    public function get_list_h($sql) {
        $result = $this->query($sql);
        $return_data = array ();
        while ($row = $this->fetch_assoc($result)) {
            $return_data[] = $row;
        }
        return $return_data;
    }
    
    public function get_one_h($sql) {
        $result = $this->query($sql);
        $return_data = array ();
        while ($row = $this->fetch_assoc($result)) {
            $return_data[] = $row;
        }
        return count($return_data)>0?$return_data[0]:false;
    }

    /*
    mysql_fetch_row()??? array? $row[0],$row[1],$row[2]
    mysql_fetch_array()? array? $row[0] 或 $row[id]
    mysql_fetch_assoc()? array? 用$row->content 字段大小写敏感
    mysql_fetch_object() object 用$row[id],$row[content] 字段大小写敏感
    */

    /*取得结果数据*/
    public function mysql_result_li() {
        return mysql_result($str);
    }

    /*取得记录集,获取数组-索引和关联,使用$row['content'] */
    public function fetch_array($resultt = "") {
        if ($resultt <> "") {
            return mysql_fetch_array($resultt);
        } else {
            return mysql_fetch_array($this->result);
        }
    }

    //获取关联数组,使用$row['字段名']
    public function fetch_assoc() {
        return mysql_fetch_assoc($this->result);
    }

    //获取数字索引数组,使用$row[0],$row[1],$row[2]
    public function fetch_row() {
        return mysql_fetch_row($this->result);
    }

    //获取对象数组,使用$row->content
    public function fetch_Object() {
        return mysql_fetch_object($this->result);
    }

    /*取得上一步 INSERT 操作产生的 ID*/
    public function insert_id() {
        return mysql_insert_id();
    }

    //指向确定的一条数据记录
    public function db_data_seek($id) {
        if ($id > 0) {
            $id = $id -1;
        }
        if (!@ mysql_data_seek($this->result, $id)) {
            $this->show_error("SQL语句有误：", "指定的数据为空");
        }
        return $this->result;
    }

    // 根据select查询结果计算结果集条数
    public function db_num_rows() {
        if ($this->result == null) {
            if ($this->show_error) {
                $this->show_error("SQL语句错误", "暂时为空，没有任何内容！");
            }
        } else {
            return mysql_num_rows($this->result);
        }
    }

    // 根据insert,update,delete执行结果取得影响行数
    public function db_affected_rows() {
        return mysql_affected_rows();
    }

    //输出显示sql语句
    public function show_error($message = "", $sql = "") {
        echo $message, $sql, "\n", mysql_error();
    }

    //释放结果集
    public function free() {
        @ mysql_free_result($this->result);
    }

    //数据库选择
    public function select_db($db_database) {
        return mysql_select_db($db_database);
    }

    //查询字段数量
    public function num_fields($table_name) {
        //return mysql_num_fields($this->result);
        $this->query("select * from $table_name");
        echo "<br />";
        echo "字段数：" . $total = mysql_num_fields($this->result);
        echo "<pre>";
        for ($i = 0; $i < $total; $i++) {
            print_r(mysql_fetch_field($this->result, $i));
        }
        echo "</pre>";
        echo "<br />";
    }

    //取得 MySQL 服务器信息
    public function mysql_server($num = '') {
        switch ($num) {
            case 1 :
                return mysql_get_server_info(); //MySQL 服务器信息
                break;

            case 2 :
                return mysql_get_host_info(); //取得 MySQL 主机信息
                break;

            case 3 :
                return mysql_get_client_info(); //取得 MySQL 客户端信息
                break;

            case 4 :
                return mysql_get_proto_info(); //取得 MySQL 协议信息
                break;

            default :
                return mysql_get_client_info(); //默认取得mysql版本信息
        }
    }
    //析构函数，自动关闭数据库,垃圾回收机制
    public function __destruct() {
        if (!empty ($this->result)) {
            $this->free();
        }
        mysql_close($this->conn);
    } //function __destruct();

    /*获得客户端真实的IP地址*/
    /** 
    * 批量插入 
    *  
    * @param string $table 要插入的表名 
    * @param array $inserts 要插入的指数据，数据的key值最好为字段名。 
    * @return boolen 
    */
    function multiInsert($table, $inserts) {
        //取出第一个要保存的数据的key值来拼field  
        $fields = "`" . implode("`,`", array_keys(current($inserts))) . "`";

        //拼接要保存的值  
        foreach ($inserts as $insert) {
            $insert = array_map('addslashes', $insert); //使用addslashes，是避免在保存的值中出现' "这些会影响sql语句的情况。一般情况下，mysql设置为：转义后的值在保存到数据库后会自动反转义。  
            $values[] = "\"" . implode("\",\"", $insert) . "\""; //拼接数据  
        }
        $valueStr = implode("),(", $values); //把数组数据拼接成字符串  

        //注意要插入的数据可能已经存在  
        $sql = "INSERT IGNORE INTO `$table` ($fields) VALUES ($valueStr)"; //重点是使用IGNORE,即遇到失败的插入直接跳过，如，纪录己存在  

        return $this->query($sql); //自定义的一个数据插入方法  
    }

	function get_bind_sql($sql, $params = array()) {
        foreach ($params as $k => $v) {
            $sql = str_replace(':' . $k, escapeSqlParam2($v), $sql);
        }
        return $sql;
    }
	function query_bind($sql, $params = array(),$needresult=false) {
        foreach ($params as $k => $v) {
            $sql = str_replace(':' . $k, escapeSqlParam2($v), $sql);
        }
        $result = $this->query($sql);
        if($needresult){
	        $return_data = array ();
	        while ($row = $this->fetch_assoc($result)) {
	            $return_data[] = $row;
	        }
	        return $return_data;
        }
        return $result;
    }
    
     function update($table,$id,$info){
        $key_sum=array_keys($info);
        $value_sum=array_values($info);
        array_walk($value_sum, 'escapeSqlParam');
   
        $raset = array();
        for($i=0;$i<count($key_sum);$i++){
            $raset[] ="$key_sum[$i]=$value_sum[$i]";
        }
        $set_str=implode(',',$raset);

        $query="update $table set $set_str where id = $id;";
        return $this->query($query);
    }
    function find($table,$info=null,$columns = "*"){
    	$where = array();
    	if($info){
	        $key_sum=array_keys($info);
	        $value_sum=array_values($info);
	        array_walk($value_sum, 'escapeSqlParam');
	        
	        for($i=0;$i<count($key_sum);$i++){
	        	$where[]=$key_sum[$i].'='.$value_sum[$i];
	        }
	    }else{
	    	$where[]='1=1';
	    }
        $query="select $columns from  $table where ".implode(' and ',$where);
        return $this->get_list_h($query,true);
    }
    function save($table,$info){
        $key_sum=array_keys($info);
        $value_sum=array_values($info);
        array_walk($value_sum, 'escapeSqlParam');
        $key_str=implode(',',$key_sum);
        $value_str=implode(',',$value_sum);
        $query="insert into $table ($key_str) values ($value_str);";
        return $this->query($query);
    }
    function delete($table,$id){        
        $id = escapeSqlParam($id);        
        $query="delete from $table where id = $id;";
        return $this->query($query);
    }

}
