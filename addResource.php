<?php
	include_once 'includes/connect.php';

openConnection();

if (isset($_FILES["DIRECTORY"])) {
    $resourceTitle = $_POST["Title"];
    $resourceDescription = $_POST['Description'];

    $DIR = $_FILES["DIRECTORY"];

    $FileName = $DIR["name"];
    $tmpName = $DIR["tmp_name"];
    $fileError = $DIR["error"];

    if ($fileError == 0) {
        if(move_uploaded_file($tmpName, "resources/$FileName")) {
            phpAlert("Your file $FileName has been uploaded.");

            //SQL insert statement, each ? represents an attribute to be saved
            $sql = "INSERT INTO resource VALUES (?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);        //preparing the sql statement
            //checking for errors
            if (!$stmt) echo $conn->error;
            $id = 0;    //id will be auto incremented
            //each param has to be entered in the correct order, according to the structure of the table.
            $stmt->bind_param("isss", $id, $FileName, $resourceDescription, $resourceTitle);
            $stmt->execute();
            $stmt->close();
            $idResource = $conn->insert_id;       //copy last id
            if (isset($_POST['idCourse']) && $_POST['idCourse'] != "none")
            {
                $idCourse = $_POST['idCourse'];
                $DateAvailable = $_POST['DateAvailable'];
                $Hidden= $_POST['Hidden'];
                //SQL insert statement, each ? represents an attribute to be saved
                $sql = "INSERT INTO courseresourcelink VALUES (?, ?, ?, ?);";
                $stmt = $conn->prepare($sql);        //preparing the sql statement
                //checking for errors
                if (!$stmt) echo $conn->error;
                //each param has to be entered in the correct order, according to the structure of the table.
                $stmt->bind_param("iiis", $idResource, $idCourse, $Hidden, $DateAvailable);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    else {
        phpAlert("Error occured during upload.");
    }
}

closeConnection();
?>
<head>
    <title>Add a Resource</title>
</head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Add a new course</h2>
            <form action="addResource.php" method="post" enctype="multipart/form-data">

                <label for="Title">Title: </label>
                <input type="text" name="Title"/>
                <label for="Description">Description</label>
                <input type="text" name="Description"/>

                <label for="DIRECTORY">Select file upload</label>
                <input type="file" name="DIRECTORY" class="Button"/>



                <br><br>
                <label for="idCourse">Course to share resource with:</label>
                <select class="listbox" name="idCourse" ><br>
                    <option value="none"> None </option>
                    <?php
                    openConnection();
                    global $conn;
                    //display a listbox with my courses.
                    $id = $_SESSION['userID'];
                    //find all courses taught by tutor
                    $sql = "SELECT CourseName, Department FROM course";
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
                <p>If none is selected, the resource will not be shared.</p>

                <label for="DateAvailable">Date available from:</label>
                <input type="date" name="DateAvailable">

                <p>Make quiz hidden?</p>
                <input type="radio" name="Hidden" id="HiddenYes" value="1">
                <label for="HiddenYes">Yes</label>
                <br>
                <input type="radio" name="Hidden" id="HiddenNo" value="0" checked>
                <label for="HiddenNo">No</label>

                <input type="submit" name="resourceUpload" value="Submit Resource">
            </form>

        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
?>
