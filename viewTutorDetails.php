<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();
if (!isset($_POST['idTutor']) && $_SESSION['type'] == "tutor")
{
    $idCurrent = $_SESSION['userID'];
}

if (isset($_POST['modify'])) {
    $id = $_POST['idTutor'];


    $FirstName = $_POST['FirstName'];
    $Surname = $_POST['Surname'];
    $Email = $_POST['Email'];
    //email check if exists
    $row = getQuery("SELECT email FROM tutor WHERE Email='$Email' and idTutor<>$id");
    if ($row)
    {
        phpAlert("This email is already registered.");
        phpRedirect("viewTutorDetails.php");
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
    $OfficeNumber = $_POST['OfficeNumber'];
    $NIN = $_POST['NIN'];

    $password = trim($_POST['password']);

    $sql = "UPDATE tutor SET FirstName='$FirstName', Surname='$Surname', Postcode='$Postcode', AddressLine1='$AddressLine1', AddressLine2='$AddressLine2', County='$County',
                   DOB='$DOB', Email='$Email', Gender='$Gender', Nationality='$Nationality', Ethnicity='$Ethnicity', MobileNumber='$MobileNumber', OfficeNumber='$OfficeNumber', 
                 NIN='$NIN', password='$password' WHERE idTutor='$id'";

    if ($password != "") {      //if password is not entered
        $sql = "UPDATE tutor SET FirstName='$FirstName', Surname='$Surname', Postcode='$Postcode', AddressLine1='$AddressLine1', AddressLine2='$AddressLine2', County='$County',
                   DOB='$DOB', Email='$Email', Gender='$Gender', Nationality='$Nationality', Ethnicity='$Ethnicity', MobileNumber='$MobileNumber', OfficeNumber='$OfficeNumber', 
                 NIN='$NIN' WHERE idTutor='$id'";
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
            <h2>Tutor Details</h2>

            <form method="post" action="viewTutorDetails.php">
            <?php
                openConnection();
                //get data
                if (isset($_POST['idTutor']) || $idCurrent != "")
                {
                    if (isset($_POST['idTutor'])) $id = $_POST['idTutor'];
                    else $id = $idCurrent;
                    $sql = "SELECT FirstName, Surname, Postcode, AddressLine1, AddressLine2, County, DOB, Email, Gender, Nationality, Ethnicity, MobileNumber, OfficeNumber, NIN FROM tutor WHERE idTutor='$id'";
                    $row = getQuery($sql);

                    foreach ($row as $key => $value)
                    {
                        if (!is_int($key)) {
                            $key = ucfirst($key);
                            echo "<label for='$key'>$key</label>
                                <input type='text' name='$key' value='$value'>";
                        }
                    }
                    echo "<label for='password'>Password:</label>
                            <input type='password' name='password'>

                            <input type='hidden' name='idTutor' value='$id'>
                          <input type='submit' name='modify' value='Modify'>";
                }
                closeConnection();
            ?>

            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>List of Courses currently teaching</h2>
            <select class="listbox" name="tutorlist" size="18">
            <?php           //finds tutors for this course and lists them in a box.
            openConnection();
            if (isset($_POST['idTutor']) && $_POST['idTutor'] != "") {
                $id = $_POST['idTutor'];
                //finds a list of tutors linked to course id $id
                $sql = "SELECT * FROM course WHERE idCourse IN ( SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')";

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
            if (isset($_POST['idTutor']) && $_POST['idTutor'] != "") {
                $id = $_POST['idTutor'];
                $sql = "SELECT FirstName, Surname, TelNum, MobileNum, PostCode, AddressLine1, AddressLine2, email FROM nok WHERE idNOK IN (SELECT Nok_idNOK FROM tutornoklink WHERE Tutor_idTutor='$id')";
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