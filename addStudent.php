<?php
include_once 'includes/connect.php';

openConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $Forename = $_POST['Forename'];
    $Surname = $_POST['Surname'];
    $Email = $_POST['Email'];

    //email check if exists
    $row = getQuery("SELECT email FROM student WHERE email='$Email'");
    if ($row)
    {
        phpAlert("This email is already registered.");
        phpRedirect("addStudent.php");
    }

    $MobileNum = $_POST['MobileNum'];
    $PostCode = $_POST['PostCode'];
    $AddressLine1 = $_POST['AddressLine1'];
    $AddressLine2 = $_POST['AddressLine2'];
    $County = $_POST['County'];
    $DOB = $_POST['DOB'];
    $Gender = $_POST['Gender'];
    $Nationality = $_POST['Nationality'];
    $Ethnicity = $_POST['Ethnicity'];

    $password = $_POST['password'];
    $password_verify = $_POST['password_verify'];

    if ($password != $password_verify)
    {
        echo "passwords dont match ";
        exit();
    }
    $password = sha1($password);

    $sql = "INSERT INTO student VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    if (!$stmt)      echo $conn->error;
    $id=0;	//id will be auto incremented
    $authorised = 0; //by default, user is not authorised.
    $stmt->bind_param("isssssssssssssi", $id, $Forename, $Surname, $PostCode, $AddressLine1, $AddressLine2, $County, $DOB, $Email, $Gender, $Nationality, $Ethnicity, $password, $MobileNum, $authorised);
    $stmt->execute();
    $stmt->close();

    //ADD NOK session values
    $_SESSION['NOK_Type'] = "student";      //indicate that the next of kin will be for student
    $studentID = $conn->insert_id;       //copy last id
    $_SESSION['relatedToID'] =  $studentID;

    //create a relationship link, based on user logged in.
    if (isset($_POST['international']) && $_POST['international'] == "1")
    {
        $VisaType = $_POST['VisaType'];
        $VisaExpiry = $_POST['VisaExpiry'];
        $VisaRefNum = $_POST['VisaRefNum'];
        $PassportNumber = $_POST['PassportNumber'];

        $sql = "INSERT INTO iStudent VALUES (?, ?, ?, ?, ?);";
        $stmt = $conn->prepare($sql);
        if (!$stmt)         echo $conn->error;
        $id=0;	//id will be auto incremented
        $stmt->bind_param("ssssi", $VisaExpiry, $PassportNumber, $VisaType, $VisaRefNum, $studentID);
        $stmt->execute();
        $stmt->close();
    }

    if (!isset($_SESSION['type'])) {
        $_SESSION['NOK_Type'] = "student";     //create session variables to save nok, if not logged in.
        $_SESSION['relatedToID'] = $studentID;
    }
    header("Location: addNOKpage.php");
    closeConnection();
}


?>
<head>
    <title>Add Student</title>
</head>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">

        <h1>Student</h1>
            <form method="post" action="addStudent.php">
                <label for="Forename">Forename </label>
                <input id="Forename" name="Forename" type="text" maxlength="255" value=""/>
                <label for="Surname">Surname </label>
                <input id="Surname" name="Surname" type="text" maxlength="255" value=""/>
                <label for="PostCode">Postcode </label>
                <input id="PostCode" name="PostCode" type="text" maxlength="255" value=""/>
                <label for="Addressline1">Address line 1 </label>
                <input id="AddressLine1" name="AddressLine1" type="text" maxlength="255" value=""/>
                <label for="AddressLine2">Address line 2 </label>
                <input id="AddressLine2" name="AddressLine2" type="text" maxlength="255" value=""/>
                <label for="County">County </label>
                <input id="Country" name="County" type="text" maxlength="255" value=""/>
                <label for="DOB">Date of birth </label>
                <input id="DOB" name="DOB" type="date" maxlength="255" value=""/>
                <p class="guidelines" id="guide_7"><small>Please input in standard DD/MM/YYYY</small></p>
                <label for="Gender">Gender </label>
                <input id="Gender" name="Gender" type="text" maxlength="255" value=""/>
                <label for="Nationality">Nationality </label>
                <input id="Nationality" name="Nationality" type="text" maxlength="255" value=""/>
                <label for="Ethnicity">Ethnicity </label>
                <input id="Ethnicity" name="Ethnicity" type="text" maxlength="255" value=""/>
                <label for="MobileNum">Mobile Number </label>
                <input id="MobileNum" name="MobileNum" type="text" maxlength="255" value=""/>


                <label for="Email">Email (used to login)</label>
                <input id="Email" name="Email" type="email" maxlength="255" value=""/>

                <label for="password" >Password:</label>
                <input id="password" name="password" type="password" maxlength="255" />
                <label for="password_verify">Password Verify:</label>
                <input  id="password_verify" name="password_verify" type="password" maxlength="255"/>



                <h3>Are you an International Student? </h3>
                <input type="radio" onclick="javascript:yesnoCheck();" name="international" id="yes" value="1">
                <label for="yes">Yes</label>
                <br>
                <input type="radio" onclick="javascript:yesnoCheck();" name="international" id="no" value="0" checked>
                <label for="no">No</label>


                <div id="inter" style="display:none">
                    <label for="VisaType">Visa type:</label>
                    <input type="text" name="VisaType">
                    <label for="VisaExpiry">Visa Expiry Date:</label>
                    <input type="date" name="VisaExpiry">
                    <label for="VisaRefNum">Visa Ref Number:</label>
                    <input type="text" name="VisaRefNum">
                    <label for="PassportNumber">Passport Number:</label>
                    <input type="text" name="PassportNumber">
                </div>

                <input type="submit" name="submit" value="Add student" />
            </form>
        </div>
    </div>
</div>


<script>
    function yesnoCheck() {
        if (document.getElementById('yes').checked) {
            document.getElementById('inter').style.display = 'block';
        }
        else document.getElementById('inter').style.display = 'none';

    }
</script>

<?php
include_once "includes/footer.php";
?>