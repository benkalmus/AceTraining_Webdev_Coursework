<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();
if (isset($_POST['QuizTitle']) )        //adding a new quiz.
{
    $QuizTitle = $_POST['QuizTitle'];
    $QuizDescription = $_POST['QuizDescription'];
    $TimeToComplete = $_POST['TimeToComplete'];

    $sql = "INSERT INTO quiz VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt)      echo $conn->error;
    $id=0;	//id will be auto incremented
    $stmt->bind_param("isss", $id, $QuizTitle, $QuizDescription, $TimeToComplete);
    $stmt->execute();
    $stmt->close();
    $idQuiz = $conn->insert_id;

    $idCourse = $_POST['courseList'];
    //add a link to course ifa valid course is selected/
    if ($idCourse != "none")
    {
        $DateAvailable = $_POST['DateAvailable'];
        $Hidden = $_POST['Hidden'];

        $sql = "INSERT INTO quizcourselink VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt)      echo $conn->error;
        $id=0;	//id will be auto incremented
        $stmt->bind_param("iisi", $idCourse, $idQuiz, $DateAvailable, $Hidden);
        $stmt->execute();
        $stmt->close();

    }
    phpAlert("Quiz has been added, redirecting to list.");
    phpRedirect("viewQuiz.php");

}

closeConnection();
?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Quiz Page</title>
</head>

<div class="row">
    <div class="leftcolumn">

        <div class="card">
            <form  name="addQuiz" method="post" action="addQuiz.php">
                <label for="QuizTitle">Title </label><br>
                <input type="text" name="QuizTitle"><br>
                <label for="QuizDescription">Quiz Description </label><br>
                <input type="text" name="QuizDescription"><br>
                <label for="TimeToComplete">Time to complete quiz (mins)</label><br>
                <input type="number" name="TimeToComplete" value="0" min="0" max="600"><br>
                <p>A value of 0 means no time restriction.</p><br> <br>

                <label for="courseList">Select which course to add the quiz to:</label><br>

                <select class="listbox" name="courseList" ><br>
                    <option value="none"> None </option>
                    <?php
                    openConnection();
                    global $conn;
                    //display a listbox with my courses.
                    $id = $_SESSION['userID'];
                    //find all courses taught by tutor
                    $sql = "SELECT * FROM course WHERE idCourse IN ( SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')";
                    $data = $conn->query($sql);
                    //$data = getQueryData($sql);
                    if (!$data) echo $conn->error;
                    while ($row = mysqli_fetch_array($data))
                    {
                        $CourseName = $row['CourseName'];
                        $Department = $row['Department'];
                        $idCourse = $row['idCourse'];
                        echo "<option value='$idCourse'>$CourseName - $Department</option>";
                    }
                    closeConnection();
                    ?>
                </select>

                <br>
                <label for="DateAvailable">Select date after which the quiz becomes available</label><br>
                <input type="date" name="DateAvailable" value="2019-01-01">
                <br><br>

                <label for="Hidden">Make quiz hidden?</label> <br>
                <input type="radio" name="Hidden" id="HiddenYes" value="1">
                <label for="HiddenYes">Yes</label>
                <br>
                <input type="radio" name="Hidden" id="HiddenNo" value="0" checked>
                <label for="HiddenNo">No</label>


                    <br>
                <input type="submit" value="Add Quiz">
            </form>
        </div>

        <div class="card">
        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>