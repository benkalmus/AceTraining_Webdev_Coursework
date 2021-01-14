<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();



closeConnection();
?>
    <head>
        <title>Resource List</title>
    </head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>

    <div class="row">
        <div class="leftcolumn">
            <div class="card">
                <h2>List of Resources
                </h2>
                <p>Search for a tutor by name </p>
                <form action="viewResources.php" method="get">
                    <input type="text" name="search">
                    <input type="submit">
                </form>

            </div>
            <?php
            openConnection();
            if (isset($_GET['search']) && $_GET['search'] != "")
            {
                $id = $_SESSION['userID'];
                $name = $_GET['search'];
                if ($_SESSION['type'] == "tutor" || $_SESSION['type'] == "admin") $sql = "SELECT * FROM resource WHERE Title LIKE '%$name%' AND idResource IN (SELECT Resource_idResource FROM courseresourcelink WHERE Course_idCourse IN (
                        SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')) ";
                else if ($_SESSION['type'] == "student")
                {
                    $sql = "SELECT * FROM resource WHERE Title LIKE '%$name%' AND idResource IN (SELECT Resource_idResource FROM courseresourcelink WHERE Course_idCourse IN (
                        SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$id')) ";
                }
            }
            else {
                $id = $_SESSION['userID'];
                if ($_SESSION['type'] == "tutor" || $_SESSION['type'] == "admin")   $sql = "SELECT * FROM resource";
                else if ($_SESSION['type'] == "student")
                {
                    $sql = "SELECT * FROM resource WHERE idResource IN (SELECT Resource_idResource FROM courseresourcelink WHERE Course_idCourse IN (
                        SELECT Course_idCourse FROM studentcourselink WHERE Student_idStudent='$id')) ";
                }
            }
            $data = $conn->query($sql);
            if (!$data) echo $conn->error;
            $num = 0;
            while (($row = mysqli_fetch_array($data)) && $num<50)
            {
                echo "<div class=\"card\">";
                $id = $row["idResource"];
                $Title = $row['Title'];
                $Description = $row['Description'];
                $File = $row['DIRECTORY'];

                $course = getQuery("SELECT CourseName FROM course WHERE idCourse IN (SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')");
                if (!$course) $CourseName = "None";
                else $CourseName = $course['CourseName'];

                echo "
                <table>
                    <tr>
                        <th>Title</th>
                        <th>$Title</th>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>$Description</td>
                    </tr>
                    <tr>
                        <td>File name</td>
                        <td>$File</td>
                    </tr>
                </table>
                ";
                echo "<form action=\"resources/$File\" method=\"get\">
                    <input type='hidden' name='idResource' value='$id'>
                    <input type='submit' name=\"Button\"  class='Button' value=\"Download\">
                </form>";
                echo "<form action=\"viewResourceDetails.php\" method=\"post\">
                    <input type='hidden' name='idResource' value='$id'>
                    <input type='submit' name=\"Button\"  class='Button' value=\"View\" >
                </form>";

                echo "</div>";
                $num++;
            }
            closeConnection();
            ?>
            <div class="card">
            </div>
        </div>
    </div>
<?php
include_once "includes/footer.php";
?>