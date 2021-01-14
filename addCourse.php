<?php
	include_once 'includes/connect.php';


	openConnection();
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		//copying POST data into local variables, has to be done for each parameter passed.
		$CourseName = $_POST['CourseName'];
        $CourseDescription = $_POST['CourseDescription'];
        $CourseStartDate = $_POST['CourseStartDate'];
        $CourseEndDate = $_POST['CourseEndDate'];
        $CourseRequirements = $_POST['CourseRequirements'];
        $Department = $_POST['Department'];
        $Subdepartment = $_POST['Subdepartment'];
		$CourseFees = $_POST['CourseFees'];


		//SQL insert statement, each ? represents an attribute to be saved
		$sql = "INSERT INTO course VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$stmt = $conn->prepare($sql);		//preparing the sql statement
		//checking for errors
		if (!$stmt)	echo $conn->error;

		$id=0;	//id will be auto incremented 
		//each param has to be entered in the correct order, according to the structure of the table.
		$stmt->bind_param("ississsss", $id, $CourseName, $CourseStartDate, $CourseFees, $CourseEndDate, $CourseDescription, $CourseRequirements, $Department, $Subdepartment);
		$stmt->execute();
		$idCourse = $conn->insert_id;
		$stmt->close();


        //TODO: create a relationship link, based on tutor logged in.
        if (isset($_POST['isTutor']) )
        {
            $lead =0;       //set Course Leader marker to 0, not leader
            if (isset($_POST['isLeadTutor'])) $lead = 1;        //if checkbox is checked, set tutor as leader
            $sql= "INSERT INTO tutorcourselink VALUES (?, ?, ?);";
		    $stmt = $conn->prepare($sql);		//preparing the sql statement
            if (!$stmt)	echo $conn->error;
            $stmt->bind_param("iii", $_SESSION['userID'], $idCourse, $lead);
            $stmt->execute();
            $stmt->close();

        }
		closeConnection();
	}



?>
<head>
    <title>Add a Course</title>
</head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Add a new course</h2>
<form action="addCourse.php" method="post">

    <label for="CourseName">Course Title: </label>
    <input type="text" name="CourseName"/>
    <label for="CourseDescription">Course Description</label>
    <input type="text" name="CourseDescription"/>
    <label for="CourseStartDate">Course Start Date</label>
    <input type="date" name="CourseStartDate"/>
    <label for="CourseEndDate">Course End Date</label>
    <input type="date" name="CourseEndDate"/>
    <label for="CourseRequirements">Course Requirements</label>
    <input type="text" name="CourseRequirements"/>
    <label for="Department">Department</label>
    <input type="text" name="Department"/>
    <label for="Subdepartment">Sub-department</label>
    <input type="text" name="Subdepartment"/>
    <label for="CourseFees">Course Fees</label>
    <input type="text" name="CourseFees"/>

    <br><br>
    <input type="checkbox" name="isTutor" id="isTutor" checked>
    <label for="isTutor">Set me as  Course Tutor</label>
    <br>
    <input type="checkbox" name="isLeadTutor" id="isLeadTutor">
    <label for="isLeadTutor">Set me as Course Leader</label>
    <br>    <br>
    <input type="submit">

</form>

        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>
