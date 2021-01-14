<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Quiz List</title>
</head>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>List of Quizzes
            </h2>
            <p>Search for a Quiz </p>
            <form action="viewQuiz.php" method="get">
                <input type="text" name="search" placeholder="Search...">
                <input type="submit">
            </form>

        </div>

        <?php
        openConnection();
        $_SESSION['type'];
        if (isset($_GET['search']) && $_GET['search'] != "")
        {
            $idStudent=$_SESSION['userID'];
            $name = $_GET['search']; // search element
            //$sql = "SELECT * FROM quiz WHERE QuizTitle LIKE '%$name%'";      //% means any characters that match (sql regex)
            switch ($_SESSION['type']){
                case "tutor":
                    $sql = "SELECT * FROM quiz WHERE QuizTitle LIKE '%$name%'";     //% means any characters that match
                    break;
                case "student":
                    //select quizzes from student's courses only.
                    $sql = "SELECT * FROM quiz WHERE QuizTitle LIKE '%$name%' AND idQuiz IN (
                            SELECT Quiz_idQuiz FROM quizcourselink WHERE Hidden=0 AND DateAvailable <= curdate() and Course_idCourse IN (
                            SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$idStudent'))";
            }
        }
        else {
            $idStudent=$_SESSION['userID'];
            switch ($_SESSION['type']){
                case "tutor":
                    $sql = "SELECT * FROM quiz";
                    break;
                case "student":
                    //select quizzes from student's courses only. WHEN THE QUIZ IS AVAILABLE DateAvailable <= curdate() and not hidden
                    $sql = "SELECT * FROM quiz WHERE idQuiz IN (SELECT Quiz_idQuiz FROM quizcourselink WHERE Hidden=0 AND DateAvailable <= curdate() AND Course_idCourse IN (
                            SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$idStudent'))";
            }
        }
        $data = $conn->query($sql);
        if (!$data) echo $conn->error;
        $num = 0;
        while (($row = mysqli_fetch_array($data)) && $num<50)
        {
            echo "<div class=\"card\">";
            $id = $row["idQuiz"];
            $Title = $row['QuizTitle'];
            $Description = $row['QuizDescription'];
            $Timer = $row['TimeToComplete'];
            $course = getQueryData("SELECT CourseName FROM course WHERE idCourse IN (SELECT Course_idCourse FROM quizcourselink WHERE Quiz_idQuiz='$id')");
            $Courses = "";
            while ($r = mysqli_fetch_array($course))
            {
                $Courses .= $r['CourseName']. ", ";
            }
            if ($Courses == "") $Courses = "None";
            //get date available
            $date = getQuery("SELECT DateAvailable FROM quizcourselink WHERE Quiz_idQuiz='$id'");
            if ($date)   $DateAvailable = $date['DateAvailable'];
            else $DateAvailable = "Not set";

            echo "
                <table>
                    <tr>
                        <th>Title</th>
                        <th>$Title</th>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>$Description</td>
                    </tr>
                    <tr>
                        <td>Timer</td>
                        <td>$Timer mins</td>
                    </tr>
                    <tr>
                        <td>Belongs to</td>
                        <td>$Courses</td>
                    </tr>
                    <tr>
                        <td>Date available</td>
                        <td>$DateAvailable</td>
                    </tr>
                </table>
                ";
            if ($_SESSION['type'] == "tutor") {
                echo "<br><form action=\"addQuestion.php\" method=\"post\">
                    <input type='hidden' name='idQuiz' value='$id'>
                    <input type='submit' name=\"Button\" class='Button'  value=\"View\">
                </form>";
            }
            else if ($_SESSION['type'] == "student")
            {
                echo "<br><form action=\"takeQuiz.php\" method=\"post\">
                    <input type='hidden' name='idQuiz' value='$id'>
                    <input type='submit' name=\"Button\" class='Button'  value=\"Take Quiz\">
                </form>";
            }


            echo "</div>";
            $num++;
        }
        closeConnection();
        ?>
        <div class="card">
        </div>
    </div>

        <?php       //DISPLAY LIST OF QUIZZES IN MY COURSES
            openConnection();
            if ($_SESSION['type'] == "tutor") {
                $idTutor = $_SESSION['userID'];
                $sql = "SELECT * FROM quiz WHERE idQuiz IN (SELECT Quiz_idQuiz FROM quizcourselink WHERE Course_idCourse IN 
                                                                         (SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor = '$idTutor'))";
                $data = getQueryData($sql);
                if (!$data) echo $conn->error;

                echo "<div class=\"rightcolumn\">
                        <div class=\"card\">
                            <h2>All quizzes in my courses</h2>
                            <form method=\"post\" action=\"addQuestion.php\">
                            <select name=\"myQuizSelect\" size=\"25\">";

                while ($row = mysqli_fetch_array($data))
                {
                    $idQuiz = $row['idQuiz'];
                    $course = getQuery("SELECT CourseName FROM course WHERE idCourse IN (SELECT Course_idCourse FROM quizcourselink WHERE Quiz_idQuiz='$idQuiz') ");
                    //also show which course the quiz is for.
                    echo "<option value='$idQuiz'>". $row['QuizTitle']. " : ". $course['CourseName']."</option>";
                }

                echo "</select>
                        <input type=\"submit\" value=\"View Quiz\">
                    </form>";

            }
        ?>



        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>