<?php
include_once 'includes/connect.php';

openConnection();

if (isset($_POST['idStudent']))
{
    $_SESSION['NOK_Type'] = "student";
    $_SESSION['relatedToID'] = $_POST['idStudent'];
}
else if (isset($_POST['idTutor']))
{
    $_SESSION['NOK_Type'] = "tutor";
    $_SESSION['relatedToID'] = $_POST['idTutor'];
}

if (isset($_POST['add']))
{
    if (isset($_POST['nokSelect']) && $_POST['nokSelect'] != "")        //user has selected a next of kin
    {
        $Relationship = $_POST['Relationship'];
        $nokID = $_POST['nokSelect'];

        saveNOKlink($nokID, $Relationship);

    }
    else {          //user created a new NOK.

        //copying POST data into local variables, has to be done for each parameter passed.
        $FirstName = $_POST['FirstName'];
        $Surname = $_POST['Surname'];
        $email = $_POST['email'];
        $TelNum = $_POST['TelNum'];
        $MobileNum = $_POST['MobileNum'];
        $PostCode = $_POST['PostCode'];
        $AddressLine1 = $_POST['AddressLine1'];
        $AddressLine2 = $_POST['AddressLine2'];
        $County = $_POST['County'];
        $Relationship = $_POST['Relationship'];

        //SQL insert statement, each ? represents an attribute to be saved
        $sql = "INSERT INTO nok VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $stmt = $conn->prepare($sql);        //preparing the sql statement
        //checking for errors
        if (!$stmt) echo $conn->error;

        $id = 0;    //id will be auto incremented
        //each param has to be entered in the correct order, according to the structure of the table.
        $stmt->bind_param("isssssssss", $id, $FirstName, $Surname, $TelNum, $MobileNum, $PostCode, $AddressLine1, $AddressLine2, $County, $email);
        //$stmt->execute();
        $stmt->close();

        $nokID = $conn->insert_id;       //copy last id
        echo "$nokID<br>";
        saveNOKlink($nokID, $Relationship);
    }
}

	function saveNOKlink($nokID, $Relationship)
    {
        global $conn;
        //create a link between two files.
        if (isset($_SESSION['NOK_Type']) && $_SESSION['NOK_Type'] != "")
        {
            if ($_SESSION['NOK_Type'] == "tutor")   //tutor link
            {
                $sql = "INSERT INTO tutornoklink VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);		//preparing the sql statement
                //checking for errors
                if (!$stmt)	echo $conn->error;
                $userID = (int)$_SESSION['relatedToID'];
                //each param has to be entered in the correct order, according to the structure of the table.
                $stmt->bind_param("iis", $nokID, $userID,  $Relationship);
                $stmt->execute();
                $stmt->close();

                $_SESSION['NOK_Type'] = "";      //clear session variables
                $_SESSION['relatedToID'] = "";

            }
            else if ($_SESSION['NOK_Type'] == "student")
            {
                $sql = "INSERT INTO studentnoklink VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);		//preparing the sql statement
                //checking for errors
                if (!$stmt)	echo $conn->error;
                $userID = (int)$_SESSION['relatedToID'];
                //each param has to be entered in the correct order, according to the structure of the table.
                $stmt->bind_param("iis", $nokID, $userID, $Relationship);
                $stmt->execute();
                $stmt->close();

                $_SESSION['NOK_Type'] = "";      //clear session variables
                $_SESSION['relatedToID'] = "";

            }

            //redirect to main page
            if ($_SESSION['type'] == "tutor" || $_SESSION['type'] == "admin")
            {
                header("Location: tutorMain.php");
            }else if ($_SESSION['type'] == "student"){
                header("Location: studentMain.php");
            }
            else header("Location: login.php");
        }
        else phpAlert("Error 00.");
    }

closeConnection();

?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>
                Select an already existing relative
            </h2>
            <form method="post" action="addNOKpage.php">
                <label for="nokSelect">Select a Relative</label>
                <select name="nokSelect">
                <?php
                //if (isset($_SESSION['type']) && $_SESSION['type'] == "admin")
                if (1)
                {     //only allow admins to add existing users, for data privacy concerns
                    //populating list with users
                    openConnection();
                    //procedural way
                    $sql = "SELECT * FROM nok";
                    $data = $conn->query($sql);
                    if (!$data) echo $conn->error;
                    $num = 0;   //avoids infinite loop
                    $row = mysqli_fetch_array($data);
                    while ($row && $num < 500)      //wont display more than 500 items in the select box
                    {
                        $id = $row['idNOK'];
                        $FirstName = $row['FirstName'];
                        $Surname = $row['Surname'];
                        $email = $row['email'];
                        echo "<option value='$id'>" . $FirstName . " " . $Surname . " - " . $email . "</option>";

                        $num++;
                        $row = mysqli_fetch_array($data);
                    }
                    closeConnection();
                    echo "
                    <label for=\"Relationship\"> Relationship to user</label>
                    <input type=\"text\" name=\"Relationship\">
                    <input type=\"submit\" name='add' value=\"Add\">";
                }
                echo "</select>";
                ?>

            </form>

        </div>
        <div class="card">

        <form action="addNOKpage.php" method="post">
            <h2>Add a new relative</h2>

            <label for="FirstName">FirstName</label>
            <input type="text" name="FirstName">
            <label for="Surname">Surname</label>
            <input type="text" name="Surname">
            <label for="email">Email</label>
            <input type="email" name="email">
            <label for="TelNum">Tel. Number </label>
            <input type="text" name="TelNum">
            <label for="MobileNum">Mobile Number </label>
            <input type="text" name="MobileNum">
            <label for="PostCode">PostCode</label>
            <input type="text" name="PostCode">
            <label for="AddressLine1">Address Line 1 and 2</label>
            <input type="text" name="AddressLine1">
            <input type="text" name="AddressLine2">
            <label for="County">County</label>
            <input type="text" name="County">
            <label for="Relationship">Relationship to user </label>
            <input type="text" name="Relationship">

            <br>
            <input type="submit" name='add' value="Add next of kin">

        </form>

        </div>
    </div>
</div>


<?php
include_once "includes/footer.php";
?>
