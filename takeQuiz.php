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

if ($_SESSION['type'] != "student")     exit();     //only allow students to take quizzes
else        //CHECK IF STUDENT IS AUTHORISED
{
    $idStudent =  $_SESSION['userID'];
    $row = getQuery("SELECT authorised FROM student WHERE idStudent='$idStudent'");
    $auth = $row['authorised'];
    //check if user is authorised
    if ($auth == 0)
    {
        phpAlert("Your account is not authorised yet. Please contact your course tutor.");
        phpRedirect("viewQuiz.php");
        exit();
    }
}

if (isset($_POST['idQuiz']) && $_POST['idQuiz'] != "")
{
    $idQuiz=$_POST['idQuiz'];
    $_SESSION['idQuiz'] = $idQuiz;


}
else if (isset($_POST['submitQuiz']) && $_SESSION['idQuiz'])
{   //submit form
    $idQuiz = $_SESSION['idQuiz'];
    $idStudent = $_SESSION['userID'];

    //get questions from the quiz, then compare answers
    $sql = "SELECT * FROM question WHERE Quiz_idQuiz='$idQuiz'";
    $data = getQueryData($sql);
    $TotalScore = 0;
    $MaximumScoreQuiz =0;
    $DateCompleted = date("Y-m-d");
    while ($row = mysqli_fetch_array($data))
    {
        $idQuestion = trim($row['idQuestion']);
        $MarksEarned = 0;
        $Marks = (int)$row['Marks'];
        $Type = $row['Type'];
        $Solution = $row['Solution'];
        $MaximumScoreQuiz += $Marks;

        switch ($Type){     //check for question type, as each question type has a different marking criteria
        case "FillIn":
            {        //simple check against the solution
                //use of strcmp function, which returns 0 if answer is perfect, and an integer depending how far off
                $userAns = trim($_POST[$idQuestion]);         //retrieving user's answer, and trimming any whitespaces
                $result = strcmp($userAns, $Solution);
                if ($result == 0)       //full marks
                {
                    $MarksEarned = $Marks;
                }
                elseif ($result >= -3 && $result <= 3)      //half marks, margin of error is +/- 3
                {
                    $MarksEarned = round($Marks / 2);        //rounding to nearest integer
                }
                else $MarksEarned = 0;      //no marks
            }break;
        case "TrueFalse":
            {
                //solution indicates which option is correct 1 or 2
                $userAns = trim($_POST[$idQuestion]);         //retrieving user's answer, and trimming any whitespaces
                if ($Solution == $userAns)
                {
                    $MarksEarned = $Marks;
                }
                else $MarksEarned=0;
            }break;
        case "Multi":
            {
                //solution would be an integer, indicating which options are correct, e.g 2.3.4
                $Question = $row['Question'];
                $pieces = explode ( '.', $Question);        //split question to individual items
                $CorrectChoice = explode ( '.', $Solution);        //split Solution to individual items

                $gotRight = 0;
                for ($i = 1; $i < count($pieces); $i++) {   //for each possible answer
                    $str = $idQuestion.$i;
                    $str = (int)$str;
                    //the key to array is the idQuestion and choice number
                    if (isset($_POST[$str])){         //check if this choice was selected.
                        if (in_array($i, $CorrectChoice)){       //check if the selected choice is in array.
                            $gotRight ++;       //if in array, this multichoice answer is correct.
                        }
                    }
                }
                $maxScore = count($CorrectChoice);     //maximum score
                $MarksEarned = round(($gotRight/$maxScore) * $Marks);      //calculate marks. simple linear equation. if user got 1/2 answers right, they get 1/2 the marks, and if they got 2/2, they get full.
            }break;
        }       //switch
        $TotalScore += $MarksEarned;        //tally up marks
    }       //while loop


    //save data to studentquizlink

    //SQL insert statement, each ? represents an attribute to be saved
    $sql = "INSERT INTO studentquizlink VALUES (?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);        //preparing the sql statement
    //checking for errors
    if (!$stmt) echo $conn->error;

    $id = 0;    //id will be auto incremented
    //each param has to be entered in the correct order, according to the structure of the table.
    $stmt->bind_param("iisi", $idQuiz, $idStudent, $DateCompleted, $TotalScore);
    $stmt->execute();
    $stmt->close();

    phpAlert("Thank you for participating in this quiz, your results have been submitted. You scored $TotalScore out of $MaximumScoreQuiz");



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
            <?php
            //DISPLAYS QUIZ DATA ON PAGE
            openConnection();
            global $idQuiz;
            $idStudent= $_SESSION['userID'];

            //find quiz details
            $sql = "SELECT * FROM quiz WHERE idQuiz='$idQuiz'";
            $row = getQuery($sql);
            $QuizTitle = $row['QuizTitle'];
            $QuizDescription = $row['QuizDescription'];
            $TimeToComplete = $row['TimeToComplete'];

            //GET MAX MARKS WORTH FOR THE QUIZ
            $sql = "SELECT Marks FROM question WHERE Quiz_idQuiz IN (SELECT idQuiz FROM quiz WHERE idQuiz='$idQuiz')";
            $data = getQueryData($sql);
            $tally = 0;
            while ($row = mysqli_fetch_array($data))
            {
                $tally += (int)$row['Marks'];
            }
            echo "<h1>$QuizTitle</h1>
                <h3>Description</h3>
                <p>$QuizDescription<br><br></p>";

            if ($TimeToComplete != 0)    echo"<p>You have $TimeToComplete minutes to complete this quiz</p>";
            else echo "<p>There are no time restrictions on this quiz, take as long as you need.</p>";

            echo "<h4>Total Marks: $tally</h4>";

            //check if student has already attempted this quiz. If so, don't let them take it again.
            $check = getQuery("SELECT * FROM studentquizlink WHERE Student_idStudent='$idStudent' AND Quiz_idQuiz='$idQuiz'");
            if ($check){
                $TotalScore = $check['TotalScore'];
                echo "<h2>You have already taken this quiz</h2>
                      <p>Your previous attempt scored $TotalScore</p>";
                exit;
            }
            echo     "<h3 id='timer' class='quizDiv'>Time Left: $TimeToComplete:00</h3>
                    <input type='button' name='Button' id='start' value='Start Quiz' onclick='startQuiz($TimeToComplete, \"timer\")'>";

            closeConnection();
            ?>
        </div>

        <form  method="post" name="quizForm" action="takeQuiz.php">
            <input type="hidden" name="submitQuiz">

        <?php
        //Display questions
        openConnection();
        global $idQuiz;     //get quiz id, global variable assigned at form load.
        $idStudent= $_SESSION['userID'];
        $return = getQuery("SELECT * FROM studentquizlink WHERE Student_idStudent='$idStudent' AND Quiz_idQuiz='$idQuiz'");
        if ($return)      exit;

        $sql = "SELECT * FROM question WHERE Quiz_idQuiz='$idQuiz'";
        $questionData = getQueryData($sql);

        echo"<div class='quiz' id='quizDiv'>";      //echoing a div, to hide content until the quiz is started.
        $num=0;
        while ($question = mysqli_fetch_array($questionData))
        {
            $idQuestion =$question['idQuestion'];
            $QuestionNumber = $question['QuestionNumber'];
            $Type = $question['Type'];
            $Title = $question['Title'];
            $Question = $question['Question'];
            $Marks = $question['Marks'];
            echo "<div class=\"card\">";
            //TODO: based on type of question, change the look. for now just a textbox.
            echo "<h2>Question #$QuestionNumber</h2>";
            switch ($Type){
                case "FillIn":
                {
                    echo "
                        <h3>$Title</h3>
                                 <h4>$Question</h4>
                        <input type='text' name='$idQuestion' placeholder='Type your answer here'>
                        <p><b>$Marks marks</b></p>
                        ";
                }break;
                case "TrueFalse":
                {
                    //format of the question is:
                    //actual question text.option1.option2
                    //delimiter is .
                    //and solution would be an integer, indicating which option is correct 1 or 2
                    $pieces = explode ( '.', $Question);
                    $text = $pieces[0];
                    $Option1 = $pieces[1];
                    $Option2 = $pieces[2];
                    echo "
                        <h3>$Title</h3>
                             <h4>$text</h4>
                        <input type='radio' name='$idQuestion' id='1$QuestionNumber' value='1'>
                        <label for='1$QuestionNumber'>$Option1</label>                     <br>
                        <input type='radio' name='$idQuestion' id='2$QuestionNumber' value='2'>
                        <label for='2$QuestionNumber'>$Option2</label>
                    ";
                }break;
                case "Multi":
                {
                    //format of the question is:
                    //actual question text.option1.option2.option3.optionN....
                    //delimiter is .
                    //and solution would be an integer, indicating which options are correct, e.g 2.3.4
                    $pieces = explode ( '.', $Question);
                    $text = $pieces[0];
                    echo "<h3>$Title</h3>
                        <h4>$text</h4>";
                    for ($i = 1; $i < count($pieces); $i++)
                    {
                        $option = $pieces[$i];
                        echo "<input type='checkbox' name='$idQuestion$i' id='$QuestionNumber$i' value='$i'>
                              <label for='$QuestionNumber$i'>$option</label>       <br>  ";
                    }
                }break;
            }
            echo"</div>";
            $num++; //keep count of question number
        }
        echo "
        <div class=\"card\">
            <p>Please make sure you have answered all questions before submitting!</p>
            <input type=\"submit\" value=\"Submit Quiz\">
            </form>
        </div>";
        echo "</div>";
        closeConnection();
        ?>
    </div>

</div>
<?php
include_once "includes/footer.php";
?>


<script>
    quiz = document.getElementById('quizDiv');
    //quiz.hidden=true;
    quiz.style.visibility = "hidden";


    function startQuiz(time, display)
    {
        //quiz = document.getElementById('quizDiv');
        //quiz.hidden=0;
        quiz.style.visibility = "visible";
        startTimer(time * 60, display)
    }

    function startTimer(duration, display) {
        var timer = duration, minutes, seconds;
        display = document.getElementById('timer');
        if (duration != 0)
        {
            setInterval(function () {
                minutes = parseInt(timer / 60, 10)
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = "Time Left: " + minutes + ":" + seconds;

                if (--timer < 0) {      //decrement timer and check if finished
                    //submit the form, even if not finished:
                    document.quizForm.submit();
                }
            }, 1000);
        }
    }

    /*window.onload = function () {
        var fiveMinutes = 60 * 5,
            display = document.getElementById('timer');
        startTimer(fiveMinutes, display);
    };*/
</script>
