
<form action="includes/signin.php" method="post">
	<input type="text" name="CourseName">
	<br>
	<input type="text" name="CourseStartDate">
	<br>
	<input type="text" name="CourseFees">

	<br>
	<input type="submit">

</form>


<?php
	include_once 'includes/connect.php';
	openConnection();

	//procedural way
	$sql = "SELECT * FROM course";
	$data = mysqli_query($conn, $sql);

	echo "Displaying all data in Courses<br>";
	while ($row = mysqli_fetch_array($data))
	{
		echo $row["idCourse"]."<br>";
		echo $row["CourseName"]."<br>";
		echo $row["CourseStartDate"]."<br>";
		echo $row["CourseFees"]."<br>";
		echo "<br>";
	}

	

	//$conn->close();		//close the connection
/*
	$servername = "localhost";
	$username = "root";
	$password = "root";
	$database = "acetraining";
	// Create connection
	$conn = new mysqli($servername, $username, $password, $database);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	echo "Connected successfully<br>";
	//object oriented way
	$result = $conn->query("SELECT * FROM course");
	$return = $result->fetch_assoc();
	echo $return["CourseName"];
	//procedural way
	$sql = "SELECT * FROM course";
	$data = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($data);
	echo $row["CourseName"];

	$conn->close();*/
?>