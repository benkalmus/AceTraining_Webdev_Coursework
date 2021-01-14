<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 26/02/2019
 * Time: 15:48
 */

include_once "includes/connect.php";

openConnection();
if (isset($_POST['idStudent']) && $_POST['idStudent'] != "")
{
    echo $_POST['idStudent'];
    $id = $_POST['idStudent'];
    $sql = "SELECT * FROM student WHERE idStudent='$id'";
    $data = $conn->query($sql);
    if (!$data) echo $conn->error;
    $row = $data->fetch_array();
    echo $row["FirstName"];
    echo $row["email"];
}

closeConnection();

?>
<head>
    <title>Students List</title>
</head>
<?php
include_once "includes/head.php";
include_once "includes/topnav.php";
?>


<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>List of Students
            </h2>
            <p>Search for a student by name </p>
            <form action="viewStudents.php" method="get">
                <input type="text" name="search">
                <input type="submit">
            </form>

        </div>
             <?php
             openConnection();


             if (isset($_GET['search']) && $_GET['search'] != "")
             {
                 $name = $_GET['search'];
                 $sql = "SELECT * FROM student WHERE FirstName LIKE '%$name%'";      //% means any characters that match
             }
             else {

                 if ($_SESSION['type'] == "tutor") {        //tutors will only see their students
                     $idTutor = $_SESSION['userID'];
                     $sql = "SELECT * FROM student WHERE idStudent IN (
                                  SELECT Student_idStudent FROM studentcourselink WHERE Course_idCourse IN (
                                  SELECT Course_idCourse FROM tutorcourselink WHERE Tutor_idTutor='$idTutor'))";
                 }
                 else
                 {
                     $sql = "SELECT * FROM student";        //admins can see all students
                 }
             }
             $data = $conn->query($sql);
             if (!$data) echo $conn->error;
             $num = 0;
             while (($row = mysqli_fetch_array($data)) && $num<50)
             {
                 echo "<div class=\"card\">";
                 $id = $row["idStudent"];
                 $FirstName = $row['FirstName'];
                 $Surname = $row['Surname'];
                 $email = $row['email'];
                 $authorised = $row['authorised'];
                 if ($authorised) $authorised="Yes"; else $authorised = "No";

                 $inter = getQuery("SELECT * FROM iStudent WHERE Student_idStudent IN (SELECT idStudent FROM student WHERE idStudent='$id')");
                 if (!$inter) $isInternational = "No";
                 else $isInternational = "Yes";

                 echo "
                <table>
                    <tr>
                        <th>Name</th>
                        <th>$FirstName $Surname</th>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>$email</td>
                    </tr>
                    <tr>
                        <td>Is Authorised?</td>
                        <td>$authorised</td>
                    </tr>
                    <tr>
                        <td>Is International?</td>
                        <td>$isInternational</td>
                    </tr>
                </table>
                ";
                 echo "<br><form action=\"viewStudentDetails.php\" method=\"post\">
                            <input type='hidden' name='idStudent' value='$id'>
                            <input type='submit' class='Button' value=\"View\">
                        </form>
                        <form action='progress.php' method='post'>
                            <input type='hidden' name='idStudent' value='$id'>
                            <input type='submit' name='progress' class='Button' value='View progress'>
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