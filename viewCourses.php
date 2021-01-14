<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

?>

<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>
<head>
    <title>Course List</title>
</head>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>List of Courses
            </h2>
            <p>Search for a course </p>
            <form action="viewCourses.php" method="get">
                <input type="text" name="search">
                <input type="submit">
            </form>

        </div>
<?php
    openConnection();
    if (isset($_GET['search']) && $_GET['search'] != "")
    {
        $name = $_GET['search']; // search element
        $sql = "SELECT * FROM course WHERE CourseName LIKE '%$name%'";      //% means any characters that match (sql magic)
    }
    else {
        //oop way
        $sql = "SELECT * FROM course";
    }
    $data = $conn->query($sql); // runs a query in SQL
    if (!$data) echo $conn->error;
    $num = 0;
    while (($row = mysqli_fetch_array($data)) && $num<50)
    {
        echo "<div class=\"card\">";
        $id = $row["idCourse"];
        $CourseName = $row["CourseName"] ;
        $Department = $row["Department"];
        $CourseStartDate = $row["CourseStartDate"];
        $CourseFees = $row["CourseFees"];
        $tutor = getQuery("SELECT FirstName, Surname FROM tutor WHERE idTutor IN (SELECT Tutor_idTutor FROM tutorcourselink WHERE Course_idCourse='$id' and isLeadTutor=1)");
        if (!$tutor) $TutorName = "None";
        else $TutorName = $tutor['FirstName'] ." " .$tutor['Surname'];

        echo "
            <table>
                <tr>
                    <th>Name</th>
                    <th>$CourseName</th>
                </tr>
                <tr>
                    <td>Department</td>
                    <td>$Department</td>
                </tr>
                <tr>
                    <td>Start Date</td>
                    <td>$CourseStartDate</td>
                </tr>
                <tr>
                    <td>Fees</td>
                    <td>£$CourseFees</td>
                </tr>
                <tr>
                    <td>Leader</td>
                    <td>$TutorName</td>
                </tr>
            </table>
            ";
        echo "<form action=\"viewCourseDetails.php\" method=\"post\">
                        <input type='hidden' name='idCourse' value='$id'>
                        <input type='submit' value=\"View\" name='Button' class='Button'>
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