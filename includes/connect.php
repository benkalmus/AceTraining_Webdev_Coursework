<?php
	define("servername", "localhost");
	define("db_username", "root");
	define("db_password", "root");
	define("database", "acetraining");

	$conn;  //global variables, allows connection to be accessed anywhere on the web site
	//object oriented way
	function openConnection()
	{
		global $conn;	//create connection
		$conn = new mysqli(servername, db_username, db_password, database);

		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		    return false;
		}
		else {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            return true;
        }
	}

	function closeConnection()
	{
		global $conn;
		$conn->close();
	}

	function getQuery($sql)     //returns an array of data from an sql query
    {
        global $conn;
        $data = $conn->query($sql);
        if (!$data) {
            echo $conn->error;
            return null;
        }
        $arr = array();
        $arr= $data->fetch_array();
        //foreach ($arr as $key => $value)   echo $key;         //display all contents
        return $arr;
    }
    function getQueryData($sql)         //returns entire data block from an sql query
    {
        global $conn;
        $data = $conn->query($sql);
        if (!$data) {
            echo $conn->error;
            return null;
        }
        return $data;
    }
    function phpAlert($msg) {
        echo '<script type="text/javascript">alert("' . $msg . '")</script>';
    }
    function phpRedirect($location)
    {
        echo "<script>
                window.location = '$location';
              </script>";
    }

?>