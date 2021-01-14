<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";


?>
    <head>
        <title>Tutors List</title>
    </head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>


    <div class="row">
        <div class="leftcolumn">
            <div class="card">
                <h2>List of Tutors
                </h2>
                <p>Search for a tutor by name </p>
                <form action="viewTutors.php" method="get">
                    <input type="text" name="search">
                    <input type="submit">
                </form>

            </div>
            <?php
            openConnection();
            if (isset($_GET['search']) && $_GET['search'] != "")
            {
                $name = $_GET['search'];
                $sql = "SELECT * FROM tutor WHERE FirstName LIKE '%$name%'";      //% means any characters that match
            }
            else {
                $sql = "SELECT * FROM tutor";
            }
            $data = $conn->query($sql);
            if (!$data) echo $conn->error;
            $num = 0;
            while (($row = mysqli_fetch_array($data)) && $num<50)
            {
                echo "<div class=\"card\">";
                $id = $row["idTutor"];
                $FirstName = $row['FirstName'];
                $Surname = $row['Surname'];
                $Email = $row['Email'];
                $OfficeNumber = $row['OfficeNumber'];

                $course = getQuery("SELECT CourseName FROM course WHERE idCourse IN (SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$id')");
                if (!$course) $CourseName = "None";
                else $CourseName = $course['CourseName'];

                echo "
                <table>
                    <tr>
                        <th>Name</th>
                        <th>$FirstName $Surname</th>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>$Email</td>
                    </tr>
                    <tr>
                        <td>Office Number</td>
                        <td>$OfficeNumber</td>
                    </tr>
                    <tr>
                        <td>Course Tutor</td>
                        <td>$CourseName</td>
                    </tr>
                </table>
                ";
                echo "<br><form action=\"viewTutorDetails.php\" method=\"post\">
                    <input type='hidden' name='idTutor' value='$id'>
                    <input type='submit' name=\"Button\"  class='Button' value=\"View\">
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