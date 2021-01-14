<?php
include_once 'connect.php';
openConnection();

if ( $_POST['CourseName'] == "" )
{
	echo "POST is empty, contact administrator<br>";
	echo "<a href='../test.php?signup=error'>Return</a>.";
}
else
{
	$CourseName = $_POST['CourseName'];
	$CourseStartDate = $_POST['CourseStartDate'];
	$CourseFees = $_POST['CourseFees'];

	//$sql = "INSERT INTO course (CourseName, CourseStartDate, CourseFees) VALUES ('$CourseName', '$CourseStartDate', '$CourseFees');";
	$sql = "INSERT INTO course VALUES (NULL, '$CourseName', '$CourseStartDate', '$CourseFees');";


	if ($conn->query($sql) === TRUE)
	{
		$last_id = $conn->insert_id;
		header("Location: ../test.php?signup=success");
	    //echo "New record created successfully. Last inserted ID is: " . $last_id;
	}
	else
	{
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
?>