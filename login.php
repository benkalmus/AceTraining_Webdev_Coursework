<?php
/**
 * Created by PhpStorm.
 * User: kalmu
 * Date: 20/02/2019
 * Time: 21:45
 */
include_once 'includes/connect.php';
openConnection();

if (isset($_POST["username"]))
{
    //check user type
    $username = $_POST['username'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];

    $password = sha1($password);

    if ($usertype == "tutor")
    {
        $sql = "SELECT * FROM tutor WHERE Email='$username' AND password='$password';";
        //find user name in mysql
        $data = $conn->query($sql);
        if (!$data)	echo $conn->error;
        else if ($data->num_rows > 0 )
        {
            $row = $data->fetch_array();
            $_SESSION['type'] = $usertype;
            $_SESSION['username'] = $username; // session keeps the webpage loaded locally
            $_SESSION['userID'] = $row['idTutor'];
            header("Location: tutorMain.php");
        }
        phpAlert("Username or password doesn't match. Please try again.");
    }
    else if ($usertype == "student")
    {
        $sql = "SELECT * FROM student WHERE email='$username' AND password='$password';";
        //find user name in mysql
        $data = $conn->query($sql);
        if (!$data)	echo $conn->error;
        else if ($data->num_rows > 0)
        {
            $row = $data->fetch_array();
            //$row = mysqli_fetch_array($data);
            $_SESSION['type'] = $usertype;
            $_SESSION['username'] = $username;
            $_SESSION['userID'] = $row['idStudent'];
            header("Location: studentMain.php");
        }
        phpAlert("Username or password doesn't match. Please try again.");
    }
    else if ($usertype == "admin")
    {
        $sql = "SELECT * FROM admin WHERE Email='$username' AND password='$password';";
        //find user name in mysql
        $data = $conn->query($sql);
        if (!$data)	echo $conn->error;
        else if ($data->num_rows > 0)
        {
            $row = $data->fetch_array();
            $_SESSION['type'] = $usertype;
            $_SESSION['username'] = $username;
            $_SESSION['userID'] = $row['idAdmin'];
            $_SESSION['isAdmin'] = 1;       //admin flag
            header("Location: tutorMain.php");
        }
        phpAlert("Username or password doesn't match. Please try again.");
    }
    closeConnection();
}
?>

    <head>
        <title>Log In </title>
    </head>

<?php
include_once  "includes/head.php";
include_once "includes/topnav.php"; // very top navigation menu/bar (main, courses etc)
?>

<div class="row">
    <div class="leftcolumn">
        <div class="card">
            <h2>Log in</h2>
            <form method="post" action="">
                <label for="username">Username (your email): </label>
                <input type="text" name="username">
                <label for="password">Password: </label>
                <input type="password" name="password">
                <p>Select your account</p>
                <input type="radio" name="usertype" id="student" value="student" checked>
                <label for="student">Student</label>
                <br>
                <input type="radio" name="usertype" id="tutor" value="tutor">
                <label for="tutor">Staff</label>
                <br>
                <input type="radio" name="usertype" id="admin" value="admin">
                <label for="admin">Admin</label>


                <br><br>
                <input type="submit" value="Login">
            </form>
        </div>
    </div>
    <div class="rightcolumn">
        <div class="card">
            <h2>About Ace training</h2>
            <p>We are fast growing tutoring company in the UK. We take pride in our work and we are the great at what we do. </p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        </div>
    </div>
</div>

<?php
include_once "includes/footer.php";
?>