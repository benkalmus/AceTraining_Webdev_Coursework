<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();

if (isset($_POST['enrol']) && $_POST['enrol'] != "" && $_SESSION['type'] == "student")      //enroll on the course
{
    $idStudent = $_SESSION['userID'];
    $idCourse = $_POST['idCourse'];
    //CHECK IF STUDENT HAS ANY WITHSTANDING FEES
    //assume no fees are unpaid
    $unpaid = false;
    $sql = "SELECT AmountPaid, Course_idCourse FROM studentcourselink WHERE Student_idStudent='$idStudent'";
    $data = getQueryData($sql);

    while ($row= mysqli_fetch_array($data))    {
        //CHECK if STUDENT HAS ALREADY ENROLLED ON THIS COURSE
        if ($row['Course_idCourse'] == $idCourse)    {
            phpAlert("You are already enrolled on this course");
            phpRedirect("viewCourses.php");
            exit();
        }
        $AmountPaid = $row['AmountPaid'];
        $idTemp = $row['Course_idCourse'];
        $course = getQuery("SELECT CourseFees FROM course WHERE idCourse='$idTemp'");
        $Fees = $course['CourseFees'];
        if ($AmountPaid < $Fees) $unpaid = true;        //found a course with unpaid fees.
    }
    if ($unpaid == true)  {
        phpAlert("You have fees left unpaid on some of your courses. You will be returned to course list.");
        phpRedirect("viewCourses.php");
        exit();
    }

    //enroll student on course
    $sql = "INSERT INTO studentcourselink VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);		//preparing the sql statement
    //checking for errors
    if (!$stmt)	echo $conn->error;
    $today = date("Y-m-d");
    $fees = 0;
    //each param has to be entered in the correct order, according to the structure of the table.
    $stmt->bind_param("iiis", $_SESSION['userID'], $_POST['idCourse'], $fees, $today );
    $stmt->execute();
    if (!$stmt)	echo $conn->error;
    $stmt->close();
    header("Location: viewCourses.php");
}

if (isset($_POST['teach']) && $_SESSION['type'] == "tutor")     //teach the course
{
    //check if already teaching this course
    $idTutor = $_SESSION['userID'];
    $idCourse = $_POST['idCourse'];
    $row = getQuery("SELECT * FROM tutorcourselink WHERE Tutor_idTutor=$idTutor AND Course_idCourse=$idCourse");
    if ($row) {
        phpAlert("You already teach this course");
        phpRedirect("viewCourses.php");
        //header("Location: viewCourses.php");
        return;
    }
    //put tutor on course
    $sql = "INSERT INTO tutorcourselink VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);		//preparing the sql statement
    //checking for errors
    if (!$stmt)	echo $conn->error;
    $lead = 0;
    if (isset($_POST['lead'])) {
        $lead =1;   //set lead mark to 1
        //remove all previous lead tutors
        $sql="UPDATE tutorcourselink SET isLeadTutor=0 WHERE Course_idCourse='$idCourse'";
        $conn->query($sql);
    }
    $stmt->bind_param("iii", $idTutor, $idCourse, $lead );
    $stmt->execute();
    if (!$stmt)	echo $conn->error;
    $stmt->close();
    phpAlert("You are now tutoring this course.");
    header("Location: viewCourses.php");
}
else if (isset($_POST['modify']))     //modify the course
{
    $idCourse = $_POST['idCourse'];
    $CourseName = $_POST['CourseName'];
    $CourseDescription = $_POST['CourseDescription'];
    $CourseStartDate = $_POST['CourseStartDate'];
    $CourseEndDate = $_POST['CourseEndDate'];
    $CourseFees = $_POST['CourseFees'];
    $CourseRequirements= $_POST['CourseRequirements'];
    $Department = $_POST['Department'];
    $Subdepartment = $_POST['Subdepartment'];
    //put tutor on course
    $sql = "UPDATE course SET CourseName='$CourseName', CourseDescription='$CourseDescription', CourseStartDate='$CourseStartDate', CourseEndDate='$CourseEndDate', CourseFees='$CourseFees',
                  CourseRequirements='$CourseRequirements', Department='$Department', Subdepartment='$Subdepartment' WHERE idCourse='$idCourse'";
    $stmt = $conn->query($sql);		//preparing the sql statement
    //checking for errors
    if (!$stmt)	echo $conn->error;
    phpAlert("Course has been modified.");
    header("Location: viewCourses.php");
}
else if (isset($_POST['leave']))
{
    $idCourse = $_POST['idCourse'];
    $idTutor = $_SESSION['userID'];
    //remove the tutor course link
    $sql ="DELETE FROM tutorcourselink WHERE Tutor_idTutor='$idTutor' AND Course_idCourse='$idCourse'";
    $check = $conn->query($sql);
    if (!$check) echo $conn->error;

    phpAlert("You no longer teach this course");
    phpRedirect("viewCourses.php");
}
else if (isset($_POST['remove']))
{
    phpAlert("Removing course.");
    $idCourse=$_POST['idCourse'];
    $sql = "DELETE FROM course WHERE idCourse='$idCourse'";
    $check = $conn->query($sql);
    if (!$check) echo $conn->error;

    phpRedirect("viewCourses.php");
}



closeConnection();
?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Course Details</title>
</head>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Course Details</h2>

            <form method="post" action="viewCourseDetails.php">
            <?php
                include_once "includes/connect.php";
                openConnection();
                //get data
                if (isset($_POST['idCourse'])) {
                    $id = $_POST['idCourse'];
                    $sql = "SELECT * FROM course WHERE idCourse='$id'";
                    $row = getQuery($sql);

                    $CourseName = $row["CourseName"];
                    $CourseStartDate = $row["CourseStartDate"];
                    $CourseFees = $row["CourseFees"];
                    $CourseEndDate = $row["CourseEndDate"];
                    $CourseDescription = $row["CourseDescription"];
                    $CourseRequirements = $row["CourseRequirements"];
                    $Department = $row["Department"];
                    $Subdepartment = $row["Subdepartment"];

                    //Find lead tutor
/*
                    $sql= "SELECT * FROM tutorcourselink WHERE Course_idCourse='$id' AND isLeadTutor=1";
                    $data = $conn->query($sql);
                    if (!$data) echo $conn->error;
                    $row = $data->fetch_array();
*/
                    $row = getQuery("SELECT * FROM tutorcourselink WHERE Course_idCourse='$id' AND isLeadTutor=1");
                    if (!$row) {        //if we got no data back, there is no lead tutor.

                        $TutorName = "No Lead Tutor for this course";
                    }
                    else{
                        $leadTutor = getQuery("SELECT * FROM tutor WHERE idTutor='$id'");
                        $TutorName = $leadTutor['FirstName']. " " . $leadTutor['Surname'];
                        //$idTutor = $row['Tutor_idTutor'];
                    }

                    echo "
                    <input type='hidden' name='idCourse' value='$id'>
                    <label for='CourseName'>Course Name</label>
                    <input type='text' name='CourseName' value='$CourseName'>
                    <label for='CourseStartDate'>Course Start Date</label>
                    <input type='date' name='CourseStartDate' value='$CourseStartDate'>
                    <label for='CourseEndDate'>Course End Date</label>
                    <input type='date' name='CourseEndDate' value='$CourseEndDate'>
                    <label for='CourseFees'>Course Fees</label>
                    <input type='text' name='CourseFees' value='$CourseFees'>
                    <label for='CourseDescription'>Course Description</label>
                    <input type='text' name='CourseDescription' value='$CourseDescription'>
                    <label for='CourseRequirements'>Course Requirements</label>
                    <input type='text' name='CourseRequirements' value='$CourseRequirements'>
                    <label for='Department'>Department</label>
                    <input type='text' name='Department' value='$Department'>
                    <label for='Subdepartment'>Sub-department</label>
                    <input type='text' name='Subdepartment' value='$Subdepartment'>
                    <label for='TutorName'>Lead Tutor</label>
                    <input type='text' name='TutorName' value='$TutorName'><br>";
                }
                else phpAlert("Please go back and select a course.");

                if (isset($_SESSION['type']) && $_SESSION['type'] == "student")
                {
                    echo "<input type=\"submit\" name=\"enrol\" value=\"Enrol\">";
                }
                else if (isset($_SESSION['type']) && $_SESSION['type'] == "tutor")
                {
                    echo "<input type=\"submit\" name=\"modify\" value=\"Modify\">";

                    //check if user is tutoring this course
                    $idTutor = $_SESSION['userID'];
                    $row = getQuery("SELECT * FROM tutorcourselink WHERE Tutor_idTutor='$idTutor' AND Course_idCourse='$id'");
                    if ($row) { //found to be a tutor
                        echo "<input type=\"submit\" name=\"leave\" value=\"Stop teaching this Course\">
                                <br><br><br><br>
                                <input type='submit' name='remove' class='Button' value='Remove this course permanently'>";
                    }
                    else {
                        echo "  <br><input type='checkbox' name='lead' id='lead' >
                                <label for='lead'><b>Lead this course</b></label><br>
                                <input type=\"submit\" name=\"teach\" value=\"Teach This Course\">";
                    }
                }
            closeConnection();
            ?>

            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>List of tutors for this course</h2>
            <select class="listbox" name="tutorlist" size="20">
            <?php           //finds tutors for this course and lists them in a box.
            openConnection();
            //get data
            if (isset($_POST['idCourse']) && $_POST['idCourse'] != "") {
                $id = $_POST['idCourse'];
                //finds a list of tutors linked to course id $id
                $sql = "SELECT * FROM tutor WHERE idTutor IN ( SELECT Tutor_idTutor FROM tutorcourselink WHERE Course_idCourse='$id')";

                $data = $conn->query($sql);
                if (!$data) echo $conn->error;
                $num = 1;
                while($row = mysqli_fetch_array($data))         //for each tutor found
                {
                    $TutorName = $row['FirstName']. " " . $row['Surname'];      //concat name
                    echo "<option>".$num.". ".$TutorName."</option>";           //display as option in a listbox
                    $num++;
                }
            }
            else echo"no post";
            closeConnection();
            ?>
            </select>
        </div>
        <div class="card">
            <h2>List of Resources</h2>
            <select class="listbox" name="resourceList" size="27">
                <?php           //finds tutors for this course and lists them in a box.
                openConnection();
                //get data
                if (isset($_POST['idCourse']) && $_POST['idCourse'] != "") {
                    $id = $_POST['idCourse'];
                    //finds a list of tutors linked to course id $id
                    $sql = "SELECT * FROM resource WHERE idResource IN ( SELECT Resource_idResource FROM courseresourcelink WHERE Course_idCourse='$id')";

                    $data = $conn->query($sql);
                    if (!$data) echo $conn->error;
                    $num = 1;
                    while($row = mysqli_fetch_array($data))         //for each tutor found
                    {
                        $Title = $row['Title']. " " . $row['DIRECTORY'];      //concat name
                        echo "<option>".$num.". ".$Title."</option>";           //display as option in a listbox
                        $num++;
                    }
                }
                else echo"no post";
                closeConnection();
                ?>
            </select>
        </div>

    </div>
</div>
<?php
include_once "includes/footer.php";
?>