<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

global $idQuiz;
openConnection();
if (isset($_POST['myQuizSelect']) && $_POST['myQuizSelect'] != "" )  //check if a quiz was selected from a listbox
{
    $idQuiz = $_POST['myQuizSelect'];
    $_SESSION['idQuiz'] = $idQuiz;
}
else if ( isset($_POST['idQuiz']) && $_POST['idQuiz'] != "")            //another check, but from the search page
{
    $idQuiz = $_POST['idQuiz'];
    $_SESSION['idQuiz'] = $idQuiz;
}
else if (isset($_SESSION['idQuiz']) && $_SESSION['idQuiz'] != "")           //if previously selcted quiz exists.
{
    $idQuiz = $_SESSION['idQuiz'];
}
else {                                                                              //otherwise leave page
    $_SESSION['idQuiz'] = ""; $idQuiz = "";
    phpAlert("Quiz is not selected, redirecting to quiz search.");
    phpRedirect("viewQuiz.php");
}

if (isset($_POST['QuestionNumber']) && isset($_SESSION['idQuiz']))      //ADDING A QUESTION
{
    //save new question
    $idQuestion = 0;
    $idQuiz = $_SESSION['idQuiz'];
    $QuestionNumber = $_POST['QuestionNumber'];
    $Type = $_POST['Type'];
    $Title = $_POST['Title'];
    $Question = $_POST['Question'];
    $Solution = $_POST['Solution'];
    $Marks = $_POST['Marks'];

    //SQL insert statement, each ? represents an attribute to be saved
    $sql = "INSERT INTO question VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);        //preparing the sql statement
    //checking for errors
    if (!$stmt) echo $conn->error;

    $id = 0;    //id will be auto incremented
    //each param has to be entered in the correct order, according to the structure of the table.
    $stmt->bind_param("iisssiis", $idQuestion, $QuestionNumber, $Title, $Question, $Solution, $idQuiz, $Marks, $Type);
    $stmt->execute();
    $stmt->close();

}
else if (isset($_POST['deleteQ']) ){        //remove selected question
    $idQuestion = $_POST['idQuestion'];
    $QuestionNumber =$_POST['deleteQ'];

    $sql = "DELETE FROM question WHERE idQuestion='$idQuestion'";
    $conn->query($sql);
    phpAlert("Question #$QuestionNumber has been removed from this quiz.");

}
else if (isset($_POST['deleteQuiz']))       //DELETE THE ENTIRE QUIZ
{
    phpAlert("Removed quiz");
    $idQuiz = $_SESSION['idQuiz'];
    $sql = "DELETE FROM quiz WHERE idQuiz='$idQuiz'";
    $r = $conn->query($sql);
    if (!$r) echo $conn->error;
    phpRedirect("viewQuiz.php");
}
else if (isset($_POST['modify'])) {                 //UPDATE quiz details
    $idQuiz = $_SESSION['idQuiz'];
    $QuizTitle = $_POST['QuizTitle'];
    $QuizDescription = $_POST['QuizDescription'];
    $TimeToComplete = $_POST['TimeToComplete'];

   //updating the actual quiz
    $sql = "UPDATE quiz SET QuizTitle='$QuizTitle', QuizDescription='$QuizDescription', TimeToComplete='$TimeToComplete' WHERE idQuiz='$idQuiz'";
    $r = $conn->query($sql);
    if (!$r) echo $conn->error;

    //updating the course-quiz link
    $idCourse = $_POST['courseList'];
    if ($idCourse != "none")
    {
        $DateAvailable = $_POST['DateAvailable'];
        $Hidden = $_POST['Hidden'];
        //First, must check if exists
        $exists = getQuery("SELECT * FROM quizcourselink WHERE Quiz_idQuiz='$idQuiz' AND Course_idCourse='$idCourse'");
        if (!$exists)       //CREATE RELATIONSHIP
        {    //SQL insert statement, each ? represents an attribute to be saved
            $sql = "INSERT INTO quizcourselink VALUES (?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);        //preparing the sql statement
            //checking for errors
            if (!$stmt) echo $conn->error;
            //each param has to be entered in the correct order, according to the structure of the table.
            $stmt->bind_param("iisi", $idCourse, $idQuiz, $DateAvailable, $Hidden);
            $stmt->execute();
            $stmt->close();
        }
        else {  //MODIFY EXISTING
            $sql = "UPDATE quizcourselink SET DateAvailable='$DateAvailable', Hidden='$Hidden' WHERE Course_idCourse='$idCourse' AND Quiz_idQuiz='$idQuiz'";
            $r = $conn->query($sql);
            if (!$r) echo $conn->error;
        }
    }
    phpAlert("Quiz has been modified");
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
            <form  method="post" action="addQuestion.php">
                <?php
                //DISPLAYS QUIZ DATA ON PAGE
                openConnection();
                global $idQuiz;

                if ($idQuiz != "")
                {
                    echo "<input type=\"hidden\" name=\"idQuiz\" value='$idQuiz'>";
                    //find quiz details
                    $sql = "SELECT * FROM quiz WHERE idQuiz='$idQuiz'";
                    $row = getQuery($sql);
                    $QuizTitle = $row['QuizTitle'];
                    $QuizDescription = $row['QuizDescription'];
                    $TimeToComplete = $row['TimeToComplete'];
                    echo "<label for=\"QuizTitle\">Title </label>";
                    echo "<input type=\"text\" name=\"QuizTitle\" value=\"$QuizTitle\">";

                    echo"<label for=\"QuizDescription\">Quiz Description </label>";
                    echo "<textarea name=\"QuizDescription\" value=\"$QuizDescription\">$QuizDescription</textarea>";

                    echo"<label for=\"TimeToComplete\">Time to complete quiz (mins)</label>";
                    echo "<input type=\"number\" name=\"TimeToComplete\" value=\"$TimeToComplete\" min=\"0\" max=\"600\">";
                    echo "<p>A value of 0 means no time restriction.</p>";

                    //GET MAX MARKS WORTH FOR THE QUIZ
                    $sql = "SELECT Marks FROM question WHERE Quiz_idQuiz IN (SELECT idQuiz FROM quiz WHERE idQuiz='$idQuiz')";
                    $data = getQueryData($sql);
                    $tally =0;
                    while ($row = mysqli_fetch_array($data))
                    {
                        $tally += (int)$row['Marks'];
                    }
                    echo "<label for='Total'>Total marks worth for quiz</label>";
                    echo "<input type='number' value='$tally' disabled>";

                }
                closeConnection();
                ?>

                <h2>Additional</h2>
                <label for="courseList">Select which course to add the quiz to:</label>

                <select class="listbox" name="courseList" >
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


                <label for="DateAvailable">Select date after which the quiz becomes available</label>
                <input type="date" name="DateAvailable" value="2019-01-01">


                <h3>Make quiz hidden?</h3>

                <input type="radio" name="Hidden" id="HiddenYes" value="1">
                <label for="HiddenYes">Yes</label>
                <br>
                <input type="radio" name="Hidden" id="HiddenNo" value="0" checked>
                <label for="HiddenNo">No</label>

                <input type="submit" name="modify" value="Modify Quiz">
                <input type="submit" name="deleteQuiz" class="Button" value="DELETE Quiz">
            </form>
        </div>


        <?php
        //TODO: Display questions added to the quiz.
        openConnection();


        global $idQuiz;     //get quiz id, global variable assigned at form load.

        $sql = "SELECT * FROM question WHERE Quiz_idQuiz='$idQuiz'";
        $questionData = getQueryData($sql);

        $num=0;
        while ($question = mysqli_fetch_array($questionData))
        {
            $idQuestion =$question['idQuestion'];
            $QuestionNumber = $question['QuestionNumber'];
            $Type = $question['Type'];
            $Title = $question['Title'];
            $Question = $question['Question'];
            $Solution = $question['Solution'];
            $Marks = $question['Marks'];

            echo "<div class=\"card\">";
            //TODO: based on type of question, change the look. for now just a textbox.
            echo "<h2>Question #$QuestionNumber</h2>";

            echo "<label for=\"Type\">Type of Question:</label>
            <input type='text' value='$Type' >
            <label for=\"Title\">Question Title:</label>
            <input type='text' value='$Title'>
            <label for=\"Question\">Question:</label>
            <input type='text' value='$Question'>
            <label for=\"Solution\">Solution to the question:</label>
            <input type='text' value='$Solution'>
            <label for=\"Marks\">Marks worth:</label>
            <input type='text' value='$Marks' >
            
            <form method='post' action='addQuestion.php'>
                <input type='hidden' name='idQuiz' value='$idQuiz'>
                <input type='hidden' name='deleteQ' value='$QuestionNumber'>
                <input type='hidden' name='idQuestion' value='$idQuestion'>
                <input type='submit' name='Button' value='Delete this question'>
            </form> 
            
            ";

            echo"</div>";
            $num++; //keep count of question number
        }
        closeConnection();
        ?>
        <div class="card">
            <h2>Add a question</h2>
            <form method="post" action="addQuestion.php">
                <label for="QuestionNumber">Question #</label>
                <?php       //GET QUESTION NUMBER
                    global $idQuiz;
                    openConnection();
                    $sql = "SELECT QuestionNumber FROM question WHERE Quiz_idQuiz='$idQuiz'";
                    $data = getQueryData($sql);
                    $max=0;
                    while ($row=mysqli_fetch_array($data))
                    {
                        $number = $row['QuestionNumber'];
                        if ($number > $max) $max=$number;
                    }
                    $max ++;
                    echo "<input type=\"number\" name=\"QuestionNumber\" value='$max'>";
                    closeConnection();
                ?>
                <label for="Type">Type of Question:</label>
                <select name="Type">
                    <option value="FillIn">Fill In</option>
                    <option value="TrueFalse">True or False</option>
                    <option value="Multi">Multi Choice</option>
                </select>
                <p><b>Format:</b><br>
                    <b>True or False</b> format of questions is: <br>
                    'insert your question here'.'option1'.'option2' <br>
                    Options are separated by . <br>
                    Solution would be an integer, indicating which option is correct, e.g 1 <br><br>
                    <b>Example:</b> Is the sky blue?.True.False<br>
                    Solution: 1<br>
                    <br>
                    <b>Multi-choice</b> format of questions is: <br>
                    'insert your question here'.'option1'.'option2'.'optionN' <br>
                    Options are separated by . <br>
                    Solution would be an integer, indicating which options are correct, e.g 1.2 <br><br>
                    <b>Example:</b> Select all noble elements.Hydrogen.Neon.Aluminium.Nitrogen.Argon<br>
                    Solution: 2.5<br>
                </p>
                <label for="Title">Question Title:</label>
                <input type="text" name="Title">
                <label for="Question">Question:</label>
                <input type="text" name="Question">
                <label for="Solution">Solution to the question:</label>
                <input type="text" name="Solution">
                <label for="Marks">Marks worth:</label>
                <input type="number" name="Marks">
                <input type="submit" value="Add a question">
            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>Courses sharing this quiz</h2>
            <select name="courselist" size="25">
            <?php   //list of courses sharing this quiz
                openConnection();
                global $idQuiz;
                $sql = "SELECT * FROM course WHERE idCourse IN (SELECT Course_idCourse FROM quizcourselink WHERE Quiz_idQuiz='$idQuiz')";
                $data = getQueryData($sql);
                if (!$data) $conn->error;
                while ($row = mysqli_fetch_array($data))
                {
                    $CourseName = $row['CourseName'];
                    $Department= $row['Department'];
                    echo "<option>$CourseName - $Department</option>";
                }
                closeConnection();
            ?>
            </select>
        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>