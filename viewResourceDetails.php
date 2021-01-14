<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */
include_once "includes/connect.php";
openConnection();


if (isset($_POST['modify']))
{
    $idResource = $_POST['idResource'];
    $Title= $_POST['Title'];
    $Description= $_POST['Description'];
    $sql = "UPDATE resource SET Title='$Title', Description='$Description' WHERE idResource='$idResource'"; //modify the resource
    $check = $conn->query($sql);
    if (!$check) echo $conn->error;
    //updating the course-resource link
    $idCourse = $_POST['courses'];
    if ($idCourse != "none")
    {
        $DateAvailable = $_POST['DateAvailable'];
        $Hidden = $_POST['Hidden'];
        //First, must check if exists
        $exists = getQuery("SELECT * FROM courseresourcelink WHERE Resource_idResource='$idResource' AND Course_idCourse='$idCourse'");
        if (!$exists)       //CREATE RELATIONSHIP
        {    //SQL insert statement, each ? represents an attribute to be saved
            $sql = "INSERT INTO courseresourcelink VALUES (?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);        //preparing the sql statement
            //checking for errors
            if (!$stmt) echo $conn->error;
            //each param has to be entered in the correct order, according to the structure of the table.
            $stmt->bind_param("iiis", $idResource, $idCourse, $Hidden, $DateAvailable);
            $stmt->execute();
            $stmt->close();
        }
        else {  //MODIFY EXISTING
            $sql = "UPDATE courseresourcelink SET DateAvailable='$DateAvailable', Hidden='$Hidden' WHERE Course_idCourse='$idCourse' AND Resource_idResource='$idResource'";
            $r = $conn->query($sql);
            if (!$r) echo $conn->error;
        }
    }
    phpAlert("Resource saved.");
}
else if (isset($_POST['remove']))
{
    phpAlert("Removing resource");
    $idResource = $_POST['idResource'];
    //first remove file on server
    $row = getQuery("SELECT DIRECTORY FROM resource WHERE idResource='$idResource'");       //find the filename
    $fileName = $row['DIRECTORY'];
    unlink("resources/".$fileName);     //removes file
    //then run sql to remove the resource from database
    $sql = "DELETE FROM resource WHERE idResource='$idResource'";
    $check = $conn->query($sql);
    if (!$check) echo $conn->error;
    phpRedirect("viewResources.php");
}
closeConnection();
?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Resource Details</title>
</head>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Resource Details</h2>
            <?php
                include_once "includes/connect.php";
                openConnection();
                //get data
                if (isset($_POST['idResource']) && $_POST['idResource'] != "")
                {
                    $id = $_POST['idResource'];
                    $sql = "SELECT Title, Description, DIRECTORY FROM resource WHERE idResource='$id'";
                    $row = getQuery($sql);
                    $filename = $row['DIRECTORY'];
                    echo "<form method='get' action='resources/$filename'>
                             <p>Click below to download the file</p>
                             <input type='submit' value='Download'>
                          </form>";
                    echo "<form method=\"post\" action=\"viewResourceDetails.php\">";
                    foreach ($row as $key => $value)
                    {
                        if (!is_int($key)) {
                            $key = ucfirst($key);
                            echo "<label for='$key'>$key</label>
                                <input type='text' name='$key' value='$value'>";
                        }
                    }
                    echo "<input type='hidden' name='idResource' value='$id'>";

                }
                closeConnection();
            ?>

                <br><br>
                <label for="idCourse">Course to share resource with:</label>
                <select class="listbox" name="courses">
                    <option value="none"> None </option>
                    <?php
                    openConnection();
                    global $conn;
                    //display a listbox with my courses.
                    $id = $_SESSION['userID'];
                    //find all courses taught by tutor
                    $sql = "SELECT CourseName, Department, idCourse FROM course";
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

                <?php
                    if ($_SESSION['type'] == "tutor" || $_SESSION['type'] == "admin") {
                        echo '<p>If none is selected, the resource will not be shared.</p>
        
                        <label for="DateAvailable">Date available from:</label>
                        <input type="date" name="DateAvailable">
        
                        <p>Make quiz hidden?</p>
                        <input type="radio" name="Hidden" id="HiddenYes" value="1">
                        <label for="HiddenYes">Yes</label>
                        <br>
                        <input type="radio" name="Hidden" id="HiddenNo" value="0" checked>
                        <label for="HiddenNo">No</label>
        
                        <input type="submit" name="modify" value="Modify">
                        <input type="submit" name="remove" class="Button" value="DELETE resource">';
                    }
                ?>


            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>List of Courses sharing this resource</h2>
            <select class="listbox" name="courseList" size="20">
            <?php           //finds tutors for this course and lists them in a box.
            openConnection();
            //get data
            if (isset($_POST['idResource']) && $_POST['idResource'] != "") {
                $id = $_POST['idResource'];
                //finds a list of tutors linked to course id $id
                $sql = "SELECT * FROM course WHERE idCourse IN ( SELECT Course_idCourse FROM courseresourcelink WHERE Resource_idResource='$id')";

                $data = $conn->query($sql);
                if (!$data) echo $conn->error;
                $num = 1;
                while($row = mysqli_fetch_array($data))         //for each tutor found
                {
                    $CourseName = $row['CourseName']. " - " . $row['Department'];      //concat name
                    echo "<option>".$num.". ".$CourseName."</option>";           //display as option in a listbox
                    $num++;
                }
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