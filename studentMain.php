<?php

include_once 'includes/connect.php';
openConnection();
//check if logged in

if (isset($_POST['pay']))           //pay outstanding fees for a course
{
    $idStudent = $_SESSION['userID'];
    $fees = $_POST['fees'];
    $idCourse= $_POST['idCourse'];
    $sql = "UPDATE studentcourselink SET AmountPaid=AmountPaid+'$fees' WHERE Course_idCourse='$idCourse' AND Student_idStudent='$idStudent'";
    $check = $conn->query($sql);
    if (!$check) echo $conn->error;
    phpAlert("You have paid £$fees into your account.");
}

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
        <div class="card">
            <h2>My Courses</h2>
            <p>List of courses you are enrolled on</p>
        </div>
            <?php

            openConnection();
            $id = $_SESSION['userID'];
            $sql = "SELECT * FROM course WHERE idCourse IN (SELECT Course_idCourse FROM studentcourselink WHERE  Student_idStudent='$id')";
            $data = getQueryData($sql);

            while($row = mysqli_fetch_array($data))
            {
                echo "<div class='card'>";
                //display title, department and fees
                $idCourse = $row['idCourse'];
                $CourseName= $row['CourseName'];
                $CourseFees = $row['CourseFees'];
                $Department = $row['Department'];
                $course = getQuery("SELECT AmountPaid FROM studentcourselink WHERE Course_idCourse='$idCourse' and Student_idStudent='$id'");
                $AmountPaid = $course['AmountPaid'];
                $StudentFees = $CourseFees - $AmountPaid;

                //count up all quizzes in course
                $quizCourseLink = getQueryData("SELECT Quiz_idQuiz FROM quizcourselink WHERE Course_idCourse='$idCourse'");
                $TotalNumQuiz = mysqli_num_rows($quizCourseLink);
                //count up all quizzes attempted
                $studentQuizLink = getQueryData("SELECT Quiz_idQuiz FROM studentquizlink WHERE Student_idStudent='$id'");
                $TotalAttempted = mysqli_num_rows($studentQuizLink);
                //calculate percentage
                if ($TotalNumQuiz != 0)              $QuizPercentage = ($TotalAttempted/$TotalNumQuiz)*100;
                else $QuizPercentage = 100;

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
                    <td>Fees</td>
                    <td>£$CourseFees</td>
                </tr>
                <tr>
                    <td>Quiz Attempts</td>
                    <td><div class='w3-light-grey w3-round-xlarge'>
                          <div class='w3-container w3-round-xlarge w3-blue w3-center' style='width:$QuizPercentage%;'>$QuizPercentage%</div>
                        </div></td>
                </tr>
                <tr>
                    <td>Outstanding Fees:</td>
                    <td style='color:darkred'>£$StudentFees</td>
                </tr>
            </table>";
                //display outstanding fees
                //give a listbox with fees to pay.
                if ($StudentFees != 0)  echo "<form method='post' action='studentMain.php'>
                                        <label for='fees'>Enter amount to pay(£):</label>
                                        <input type='hidden' name='idCourse' value='$idCourse'>
                                        <input type='number' name='fees' min='0' value='0' max='$StudentFees'>
                                        <input type='submit' name='pay' value='Pay'>
                                    </form>";
                echo "</div>";
            }
            closeConnection();
            ?>

    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>About</h2>
            <p>
                <?php
                openConnection();
                $userID = $_SESSION['userID'];
                $sql = "SELECT * FROM student WHERE idStudent='$userID'";
                $data = $conn->query($sql);
                if (!$data)	echo $conn->error;
                else{
                    $row = mysqli_fetch_array($data);
                    echo "Welcome " . $row['FirstName'] ." ". $row['Surname']. ",";
                }

                $sql = "SELECT * FROM istudent WHERE Student_idStudent='$userID'";
                $row = getQuery($sql);
                if ($row){

                    echo "<br> International Student";
                }
                closeConnection();
                ?>

            </p>
        </div>
        <div class="card">
            <h3>Additional info</h3>
            <?php   //display NOK info
                openConnection();
                $id = $_SESSION['userID'];
                $row = getQuery("SELECT FirstName, Surname, email, MobileNum FROM nok WHERE idNOK IN (SELECT NOK_idNOK FROM studentnoklink WHERE Student_idStudent='$id' )");
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
                            <input type='hidden' name='idStudent' value='$id'>
                            <input type='submit' value='Go'> 
                        </form>";

                }
                closeConnection();
            ?>
        </div>
    </div>
</div>

<?php
include_once "includes/footer.php";
?>
