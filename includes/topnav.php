<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 21/02/2019
 * Time: 15:46
 */

include_once "connect.php";

?>

<ul>
    <?php
    if (isset($_SESSION['type']))
    {   //shared
        echo '<li style="float:right">
                <a href="logout.php" >Log Out</a>
                </li>';

        $type = $_SESSION['type'];
        switch ($type)          //display different menus depending on the user logged in.
        {
            case "student":
            {
                //main
                echo ' <li><a href="studentMain.php">Main</a></li>';
                //display my student info
                echo "<li>
                        <a href=\"viewStudentDetails.php\" >
                            My Account
                        </a>
                    </li>";
                //display courses
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Course</a>
                            <div class=\"dropdown-content\">
                                <a href='viewCourses.php'>View List</a>
                                <a href='progress.php'>View Progress</a>
                            </div>
                        </li>";
                //display quizzes
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Quiz</a>
                            <div class=\"dropdown-content\">
                                <a href='viewQuiz.php'>View List</a>
                            </div>
                        </li>";
                //display resources
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Resources</a>
                            <div class=\"dropdown-content\">
                                <a href='viewResources.php'>View List</a>
                            </div>
                        </li>";

            }break;
            case "tutor":
            {
                //main
                echo ' <li><a href="tutorMain.php">Main</a></li>';
                //display my student info
                echo "<li>
                        <a href=\"viewTutorDetails.php\" >
                            My Account
                        </a>
                    </li>";
                //display courses
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Courses</a>
                            <div class=\"dropdown-content\">
                                <a href='viewCourses.php'>View List</a>
                                <a href='addCourse.php'>Add New</a>
                            </div>
                        </li>";
                //display students
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Students</a>
                            <div class=\"dropdown-content\">
                                <a href='viewStudents.php'>View List</a>
                                <a href='addStudent.php'>Add New</a>
                            </div>
                        </li>";
                //display quizzes
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Quiz</a>
                            <div class=\"dropdown-content\">
                                <a href='viewQuiz.php'>View List</a>
                                <a href='addQuiz.php'>Add New</a>
                            </div>
                        </li>";
                //display resources
                echo "  <li class=\"dropdown\">
                            <a href=\"javascript:void(0)\" class=\"dropbtn\">Resources</a>
                            <div class=\"dropdown-content\">
                                <a href='viewResources.php'>View List</a>
                                <a href='addResource.php'>Add New</a>
                            </div>
                        </li>";
            } break;
            case "admin":
            {

            } break;
        }
    }
    else{
        //return to main
        echo "
        <li>
            <a href=\"login.php\" >
                Main menu
            </a>
        </li>";
        //register admin
        echo "
        <li style=\"float:right\">
            <a href=\"addAdmin.php\" >
                Register Admin
            </a>
        </li>";
        //reg student
        echo "
        <li style=\"float:right\">
            <a href=\"addStudent.php\" >
                Register Student
            </a>
        </li>";
        //login
        echo '<li style="float:right">
                <a href="login.php" >Login</a>
                </li>';

    }
    ?>


</ul>
