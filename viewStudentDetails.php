<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();

if (isset($_POST['authorise']))
{
    $idStudent = $_POST['idStudent'];
    $sql ="UPDATE student SET authorised=1 WHERE idStudent='$idStudent'";
    $conn->query($sql);
    phpAlert("Student has been authorised");
}
else if (isset($_POST['deauth']))
{
    $idStudent = $_POST['idStudent'];
    $sql ="UPDATE student SET authorised=0 WHERE idStudent='$idStudent'";
    $conn->query($sql);
    phpAlert("Student has been de-authorised");
}

if (!isset($_POST['idStudent']))
{
    global $idCurrent;
    $idCurrent = $_SESSION['userID'];
}
//modify data
if (isset($_POST['modify'])) {
    $id = $_POST['idStudent'];
    $FirstName = $_POST['FirstName'];
    $Surname = $_POST['Surname'];
    $Email = $_POST['Email'];
    //email check if exists
    $row = sqlQuery("SELECT email FROM student WHERE email='$Email' and idStudent<>$id");
    if ($row)
    {
        phpAlert("This email is already registered.");
        phpRedirect("addStudent.php");
    }
    $MobileNumber = $_POST['MobileNumber'];
    $Postcode = $_POST['Postcode'];
    $AddressLine1 = $_POST['AddressLine1'];
    $AddressLine2 = $_POST['AddressLine2'];
    $County = $_POST['County'];
    $DOB = $_POST['DOB'];
    $Gender = $_POST['Gender'];
    $Nationality = $_POST['Nationality'];
    $Ethnicity = $_POST['Ethnicity'];

    $password = trim($_POST['password']);
    $sql = "UPDATE student SET FirstName='$FirstName', Surname='$Surname', Postcode='$Postcode', AddressLine1='$AddressLine1', AddressLine2='$AddressLine2', County='$County',
            DOB='$DOB', email='$Email', Gender='$Gender', Nationality='$Nationality', Ethnicity='$Ethnicity', MobileNumber='$MobileNumber', password='$password' WHERE idStudent='$id'";
    if ($password != "") {
        $sql = "UPDATE student SET FirstName='$FirstName', Surname='$Surname', Postcode='$Postcode', AddressLine1='$AddressLine1', AddressLine2='$AddressLine2', County='$County',
                   DOB='$DOB', email='$Email', Gender='$Gender', Nationality='$Nationality', Ethnicity='$Ethnicity', MobileNumber='$MobileNumber' WHERE idStudent='$id'";
    }

    $check = $conn->query($sql);
    if (!$check) echo $conn->error;
    else phpAlert("Details have been successfully updated.");

}

closeConnection();
?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Student Details</title>
</head>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Student Details</h2>

            <form method="post" action="viewStudentDetails.php">
            <?php
                include_once "includes/connect.php";
                openConnection();
                //get data
                if (isset($_POST['idStudent']) || $idCurrent != "")
                {
                    if (isset($_POST['idStudent'])) $id = $_POST['idStudent'];
                    else $id = $idCurrent;
                    $sql = "SELECT FirstName, Surname, Postcode, AddressLine1, AddressLine2, County, DOB, email, Gender, Nationality, Ethnicity, MobileNumber, authorised FROM student WHERE idStudent='$id'";
                    $row = getQuery($sql);

                    foreach ($row as $key => $value)
                    {
                        if (!is_int($key)) {
                            $key = ucfirst($key);
                            echo "<label for='$key'>$key</label>
                                <input type='text' name='$key' value='$value'>";
                        }
                    }
                    echo "<input type='hidden' name='idStudent' value='$id'>
                            <label for='password'>Password:</label>
                            <input type='password' name='password'>";

                    $sql = "SELECT VisaType, VisaRefNum, VisaExpiry, PassportNumber FROM istudent WHERE Student_idStudent='$id'";
                    $row = getQuery($sql);
                    if ($row) {
                        echo "<h3>International Student details</h3>";
                        foreach ($row as $key => $value) {
                            if (!is_int($key)) {
                                $key = ucfirst($key);
                                echo "<label for='$key'>$key</label>
                                <input type='text' name='$key' value='$value'>";
                            }
                        }
                    }

                }
                closeConnection();
            ?>
            <?php
            if ($_SESSION['type'] == "tutor" || $_SESSION['type'] == "admin" )
            {
                echo "<input type=\"submit\" class=\"Button\" name=\"authorise\" value=\"Authorise Student\">
                        <input type=\"submit\" class=\"Button\" name=\"deauth\" value=\"Deauthorise Student\">";
            }
            else
                echo "<input type='submit' name='modify' value='Modify'>";
            ?>

            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>List of Courses enrolled</h2>
            <select class="listbox" name="tutorlist" size="20">
            <?php           //finds tutors for this course and lists them in a box.
            openConnection();
            //get data
            if (isset($_POST['idStudent']) || $idCurrent != "") {
                if (isset($_POST['idStudent'])) $id = $_POST['idStudent'];
                else $id = $idCurrent;
                //finds a list of tutors linked to course id $id
                $sql = "SELECT * FROM course WHERE idCourse IN ( SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$id')";

                $data = $conn->query($sql);
                if (!$data) echo $conn->error;
                $num = 1;
                while($row = mysqli_fetch_array($data))         //for each tutor found
                {
                    $CourseName = $row['CourseName']. " " . $row['Department'];      //concat name
                    echo "<option>".$num.". ".$CourseName."</option>";           //display as option in a listbox
                    $num++;
                }
            }
            closeConnection();
            ?>
            </select>
        </div>
        <div class="card">
            <?php
            openConnection();
            //get data
            if (isset($_POST['idStudent']) || $idCurrent != "") {
                if (isset($_POST['idStudent'])) $id = $_POST['idStudent'];
                else $id = $idCurrent;
                $sql = "SELECT FirstName, Surname, TelNum, MobileNum, PostCode, AddressLine1, AddressLine2, email FROM nok WHERE idNOK IN (SELECT Nok_idNok FROM studentnoklink WHERE Student_idStudent='$id')";
                $row = getQuery($sql);
                if ($row) {
                    echo "<h3>Next of Kin details</h3>";
                    foreach ($row as $key => $value) {
                        if (!is_int($key)) {
                            $key = ucfirst($key);
                            echo "<label for='$key'>$key</label>
                                  <input type='text' name='$key' value='$value'>";
                        }
                    }
                }
            }
            closeConnection();
            ?>
        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>