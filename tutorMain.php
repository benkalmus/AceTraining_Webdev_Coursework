<?php
include_once 'includes/connect.php';
openConnection();
//check if logged in, and is admin or tutor, otherwise log them out.
if ( !isset($_SESSION['type']) ) header("Location: logout.php");

if ( $_SESSION['type'] !="tutor" && $_SESSION['type'] != "admin" ) header("Location: logout.php");



closeConnection();
?>
<head>
    <title>Main menu</title>
</head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <?php
        if ($_SESSION['type'] == "admin")
        {
            echo "
            <div class='card'>
            <h2>Add a tutor</h2>
            <form action='addTutor.php'>
                <input type='submit' value='Add'>
            </form>
            </div>";
        }
        ?>

        <div class="card">
            <h2>New students</h2>
            <p>Students awaiting authorisation:</p>
            <?php
            openConnection();
            //finds all students in courses taught by this tutor
            $idTutor= $_SESSION['userID'];
            $sql = "SELECT * FROM student WHERE authorised=0 AND idStudent IN (SELECT Student_idStudent FROM studentcourselink WHERE Course_idCourse IN (
                    SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$idTutor'))";
            $data = $conn->query($sql);
            if (!$data) echo $conn->error;
            $num = 0;
            while (($row = mysqli_fetch_array($data)) && $num<50)
            {
                echo "<div class=\"card\">";
                $id = $row["idStudent"];
                $FirstName = $row['FirstName'];
                $Surname = $row['Surname'];
                $email = $row['email'];
                $authorised = $row['authorised'];
                //find out which courses they are enrolled on
                $course = getQueryData("SELECT * FROM course WHERE idCourse IN (SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$id')");
                $Courses = "";
                while ($r = mysqli_fetch_array($course))
                {
                    $Courses .= $r['CourseName']. ", ";
                }
                if ($Courses == "") $Courses = "None";
                echo "
                <table>
                    <tr>
                        <th>Name</th>
                        <th>$FirstName $Surname</th>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>$email</td>
                    </tr>
                    <tr>
                        <td>Authorised</td>
                        <td>$authorised</td>
                    </tr>
                    <tr>
                        <td>Enrollment</td>
                        <td>$Courses</td>
                    </tr>
                </table>
                ";
                echo "<br><form action=\"viewStudentDetails.php\" method=\"post\">
                    <input type='hidden' name='idStudent' value='$id'>
                    <input type='submit' class='Button' name=\"View\" value=\"View\">
                    <input type='submit' class='Button' name=\"authorise\" value=\"Authorise\">
                </form>";

                echo "</div>";
                $num++;
            }
            ?>
        </div>
        <div class="card">
            <?php           //displaying courses
                openConnection();
                echo "<h2>Jump to course</h2>";
                $id = $_SESSION['userID'];
                $sql = "SELECT * FROM course WHERE idCourse IN (SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')";
                $data = getQueryData($sql);
                while ($row = mysqli_fetch_array($data))
                {
                    $idCourse=$row['idCourse'];
                    $CourseName=$row['CourseName'];
                    $Department= $row['Department'];
                    $CourseStartDate= $row['CourseStartDate'];

                    echo "
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>$CourseName</th>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>$Department</td>
                        </tr>
                        <tr>
                            <td>Start Date</td>
                            <td>$CourseStartDate</td>
                        </tr>
                    </table>
                    ";
                    echo "<br><form action=\"viewCourseDetails.php\" method=\"post\">
                        <input type='hidden' name='idCourse' value='$idCourse'>
                        <input type='submit' class='Button' name=\"View\" value=\"View\">
                    </form>";
                }
                closeConnection();
            ?>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>About</h2>
            <p>
                <?php
                openConnection();
                $userID = $_SESSION['userID'];
                $sql = "SELECT * FROM admin WHERE idAdmin='$userID'";      //for admin
                if ($_SESSION['type'] == "tutor")   $sql = "SELECT * FROM tutor WHERE idTutor='$userID'";
                $data = $conn->query($sql);
                if (!$data)	echo $conn->error;
                else{
                    $row = mysqli_fetch_array($data);
                    echo "Welcome back " . $row['FirstName'] ." ". $row['Surname']. ",";
                }
                closeConnection();
                ?>
            </p>
        </div>
        <div class="card">
            <h3>Quiz info</h3>
            <p>List of quizzes about to become available in the next week.</p>
            <select size="10">
            <?php       //Display Quizzes about to become Available
                openConnection();
                $today = date("Y-m-d");
                $lastweek = date("Y-m-d", strtotime("-7 days", strtotime($today)));
                $sql = "SELECT * FROM quizcourselink WHERE Hidden=0 AND (DateAvailable BETWEEN '$lastweek' AND '$today')";
                $data= getQueryData($sql);
                if (!$data) echo $conn->error;
                while ($row=mysqli_fetch_array($data))
                {
                    $idCourse = $row['Course_idCourse'];
                    $course = getQuery("SELECT CourseName FROM course WHERE idCourse='$idCourse'");
                    $CourseName=$course['CourseName'];

                    $idQuiz = $row['Quiz_idQuiz'];
                    $quiz = getQuery("SELECT * FROM quiz WHERE idQuiz='$idQuiz'");
                    $QuizTitle = $quiz['QuizTitle'];
                    $Date = $row['DateAvailable'];
                    echo "<option>$QuizTitle -$CourseName -$Date</option>";
                }
                closeConnection();
            ?>
            </select>
        </div>
        <div class="card">
            <h3>Additional info</h3>
            <?php   //display NOK info
            openConnection();
            $id = $_SESSION['userID'];
            $row = getQuery("SELECT FirstName, Surname, email, MobileNum FROM nok WHERE idNOK IN (SELECT NOK_idNOK FROM tutornoklink WHERE Tutor_idTutor='$id' )");
            if ($row)
            {
                echo "<p>Next of Kin</p>";
                foreach ($row as $key => $value) {
                    if (!is_int($key)) {
                        $key = ucfirst($key);
                        echo "<label for='$key'>$key:</label>
                                        <input type='text' name='$key' value='$value' disabled>";
                    }
                }
            }
            else
            {
                echo "<p>You must add a next of kin for security reasons. Please click on the button below to continue.</p>
                            <form method='post' action='addNOKpage.php'>
                            <input type='hidden' name='idTutor' value='$id'>
                            <input type='submit' value='Go'> 
                        </form>";

            }
            closeConnection();
            ?>
        </div>
    </div>
</div>

<div class="footer">
    <h3>Footer</h3>
</div>

</body>
</html>
