<?php

include_once 'includes/connect.php';
openConnection();
global $id;
if (isset($_POST['idStudent']))
{
    $id=$_POST['idStudent'];
}
else if ($_SESSION['type'] == "student") $id = $_SESSION['userID'];

closeConnection();
?>
    <head>
        <title>Progress page</title>
    </head>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>


<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>My Courses</h2>
        </div>
            <?php
            openConnection();
            if ($id != "") {
                $id = $_SESSION['userID'];
                $idStudent = $_SESSION['userID'];
                $sql = "SELECT * FROM course WHERE idCourse IN (SELECT Course_idCourse FROM studentcourselink WHERE  Student_idStudent='$id')";
                $data = getQueryData($sql);

                while ($row = mysqli_fetch_array($data)) {
                    echo "<div class='card'>";
                    //display title, department and fees
                    $idCourse = $row['idCourse'];
                    $CourseName = $row['CourseName'];
                    $Department = $row['Department'];
                    //get course dates
                    $CourseStartDate = new DateTime($row['CourseStartDate']);
                    $CourseEndDate = new DateTime($row['CourseEndDate']);
                    $today = new DateTime(date("Y-m-d"));
                    //find number of days between two dates
                    $CourseDays = $CourseEndDate->diff($CourseStartDate)->format("%a");     //get difference in days, length of course
                    //find number of days between today and course start date
                    $Days = $today->diff($CourseStartDate)->format("%a");                   //how many days into the course
                    if ($CourseDays != 0) $DaysPercentage = ($Days / $CourseDays) * 100;
                    $DaysPercentage = 100;
                    if ($today > $CourseEndDate) $DaysPercentage = 100;
                    if ($today < $CourseStartDate) $DaysPercentage = 0;

                    //count up all quizzes in course
                    $quizCourseLink = getQueryData("SELECT Quiz_idQuiz FROM quizcourselink WHERE Course_idCourse='$idCourse'");
                    $TotalNumQuiz = mysqli_num_rows($quizCourseLink);
                    //count up all quizzes attempted
                    $studentQuizLink = getQueryData("SELECT Quiz_idQuiz FROM studentquizlink WHERE Student_idStudent='$id'");
                    $TotalAttempted = mysqli_num_rows($studentQuizLink);
                    //calculate percentage
                    if ($TotalNumQuiz != 0) $QuizPercentage = ($TotalAttempted / $TotalNumQuiz) * 100;
                    else $QuizPercentage = 100;

                    //calculate total Score for quizzes done
                    $quiz = getQueryData("SELECT SUM(TotalScore) AS TotalScore FROM studentquizlink WHERE Student_idStudent='$idStudent' AND Quiz_idQuiz IN (
                                                                          SELECT Quiz_idQuiz FROM quizcourselink WHERE Hidden=0 AND DateAvailable<=curdate() AND Course_idCourse='$idCourse')");
                    $Qrow = mysqli_fetch_array($quiz);
                    $TotalScore = $Qrow['TotalScore'];

                    $quiz = getQueryData("SELECT SUM(Marks) AS TotalMarks FROM question WHERE Quiz_idQuiz IN (
                                                                          SELECT Quiz_idQuiz FROM quizcourselink WHERE Course_idCourse='$idCourse')");
                    $Qrow = mysqli_fetch_array($quiz);
                    $TotalMarks = $Qrow['TotalMarks'];

                    if ($TotalMarks != 0) $MarksPercentage = ($TotalScore / $TotalMarks) * 100;
                    else $MarksPercentage = 100;

                    $CourseStartDate = $CourseStartDate->format("Y-m-d");
                    $CourseEndDate = $CourseEndDate->format("Y-m-d");
                    echo "<table>
                        <tr>
                            <th>Name</th>
                            <th>$CourseName</th>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>$Department</td>
                        </tr>
                        <tr>
                            <td>Course Progress: $CourseStartDate - $CourseEndDate</td>
                            <td><div class='w3-light-grey w3-round-xlarge'>
                                  <div class='w3-container w3-round-xlarge w3-red w3-center' style='width:$DaysPercentage%; '>$DaysPercentage%</div>
                                </div></td>
                        </tr>
                        <tr>
                            <td>Quiz Attempts</td>
                            <td><div class='w3-light-grey w3-round-xlarge'>
                                  <div class='w3-container w3-round-xlarge w3-blue w3-center' style='width:$QuizPercentage%;'>$QuizPercentage%</div>
                                </div></td>
                        </tr>
                        <tr>
                            <td>Quiz Average Score</td>
                            <td><div class='w3-light-grey w3-round-xlarge'>
                                  <div class='w3-container w3-round-xlarge w3-green w3-center' style='width:$MarksPercentage%; '>$MarksPercentage%</div>
                                </div></td>
                        </tr>
                    </table>";
                    echo "</div>";
                }
            }
            closeConnection();
            ?>

    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>Student Details</h2>
            <?php   //pull up student details
            if ($id != "") {
                openConnection();
                $id = $_SESSION['userID'];
                $row = getQuery("SELECT FirstName, Surname, email FROM student WHERE idStudent='$id'");
                if ($row) {
                    foreach ($row as $key => $value) {
                        if (!is_int($key)) {
                            $key = ucfirst($key);
                            echo "<label for='$key'>$key:</label>
                                                        <input type='text' name='$key' value='$value' disabled>";
                        }
                    }
                }
                closeConnection();
            }
            ?>
        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>