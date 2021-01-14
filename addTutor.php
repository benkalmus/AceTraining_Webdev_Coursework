<?php
	include_once 'includes/connect.php';


	openConnection();
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		//copying POST data into local variables, has to be done for each parameter passed.
		$FirstName = $_POST['FirstName'];
		$Surname = $_POST['Surname'];
		$PostCode = $_POST['PostCode'];
		$AddressLine1 = $_POST['AddressLine1'];
		$AddressLine2 = $_POST['AddressLine2'];
		$County = $_POST['County'];
		$DOB = $_POST['DOB'];
		$Email = $_POST['Email'];
        //email check if exists
        $row = getQuery("SELECT email FROM tutor WHERE email='$Email'");
        if ($row)
        {
            phpAlert("This email is already registered.");
            phpRedirect("addTutor.php");
        }
		$Gender = $_POST['Gender'];
		$Nationality = $_POST['Nationality'];
		$Ethnicity = $_POST['Ethnicity'];
		$OfficeNumber = $_POST['OfficeNumber'];
        $Mobile = $_POST['Mobile'];
		$NIN = $_POST['NIN'];
        $password = $_POST['password'];
        $password_verify = $_POST['password_verify'];

        if ($password != $password_verify)
        {
            echo "passwords dont match";
            exit();
        }

        $password = sha1($password);
		//SQL insert statement, each ? represents an attribute to be saved
		$sql = "INSERT INTO tutor VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";		//this produces a template for mysql query
		$stmt = $conn->prepare($sql);		//preparing the sql statement
		//checking for errors
		if (!$stmt)	echo $conn->error;

		$id=0;	//id will be auto incremented 
		//each param has to be entered in the correct order, according to the structure of the table.
		$stmt->bind_param("isssssssssssssss", $id, $FirstName, $Surname, $PostCode, $AddressLine1, $AddressLine2, $County, $DOB, $Email, $Gender, $Nationality, $Ethnicity, $OfficeNumber, $Mobile, $NIN, $password);
		$stmt->execute();
		$stmt->close();
		//saved tutor, now add a next of kin

        $_SESSION['NOK_Type'] = "tutor";      //indicate that the next of kin will be for student
        $_SESSION['relatedToID'] = $conn->insert_id;


        header("Location: addNOKpage.php");


		//TODO: create a relationship link, based on user logged in.

        //TODO: show the user their ID.
		closeConnection();
		echo "saved";
	}


?>
<head>
    <title>Add a Tutor</title>
</head>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>
                Personal details
            </h2>
        <form action="addTutor.php" method="post">
            <label for="FirstName">Firstname</label>
            <input type="text" name="FirstName">
            <label for="Surname">Surname</label>
            <input type="text" name="Surname">
            <label for="PostCode">Post code</label>
            <input type="text" name="PostCode">
            <label for="AddressLine1">Address Line 1 and 2</label>
            <input type="text" name="AddressLine1">
            <input type="text" name="AddressLine2">
            <label for="County">County</label>
            <input type="text" name="County">
            <label for="DOB">Date of Birth</label>
            <input type="date" name="DOB">
            <label for="Gender">Gender</label>
            <input type="text" name="Gender">
            <label for="Nationality">Nationality</label>
            <input type="text" name="Nationality">
            <label for="Ethnicity">Ethnicity</label>
            <input type="text" name="Ethnicity">
            <label for="OfficeNumber">Office Number</label>
            <input type="text" name="OfficeNumber">
            <label for="NIN">National Insurance Number</label>
            <input type="text" name="NIN">
            <label for="Mobile">Mobile</label>
            <input type="text" name="Mobile">

            <br>
            <label for="Email">Email (used for login)</label>
            <input type="email" name="Email">
            <label for="password">Password</label>
            <input type="password" name="password">
            <label for="password_verify">Verify password: </label>
            <input type="password" name="password_verify">

            <br>
            <input type="submit">

        </form>
        </div>


    </div>
</div>
<?php
include_once "includes/footer.php";
?>
