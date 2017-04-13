<?php
session_start();
function check_login($flag,$username,$pass){
	//echo $_SESSION['is_login'];
	if(!isset($_SESSION[$flag])){			
            $inputusername= isset($_POST['username']) ? $_POST['username'] : $_GET['username'];
            $inputpass= isset($_POST['password']) ? $_POST['password'] : $_GET['password'];
            if($inputusername == $username && $inputpass == $pass){
                $_SESSION[$flag] = 1;
            }else{
		        require("input.php");
                exit();
	    }	
	}
}

check_login('edata',"admin","168168");
