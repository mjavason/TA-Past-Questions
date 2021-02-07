<?php
session_start();

function Sanitize($data, $case = null)
{
    //This function cleanses and arranges the data about to be stored. like freeing it from any impurities like sql injection
    $result = htmlentities($data, ENT_QUOTES);
    if ($case == 'lower') {
        $result = strtoupper($result);
    } elseif ($case == 'none') {
        //leave it as it is
    } else {
        $result = strtoupper($result);
    }
    return $result;
}

function fetchAdmin($formData)
{
    //Somtoo's Function

    global $db;
    extract($formData);
    $sql = "SELECT * FROM `admins` WHERE `email`='$email' AND `password`='$password' ";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        return $result;
    } else {
        return array();
    }
}

function processRegister($formstream)
{
    //This function processes what user data is being stored and checks if they are accurate or entered at all.
    //It also helps in confirming if what the user entered is Okay, like someone entering two different things in the password and confirm password box
    extract($formstream);

    if (isset($submit)) {

        $data_missing = [];

        if (empty($firstname)) {
            $data_missing['fname'] = "Missing First Name";
        } else {
            $firstname = trim(Sanitize($firstname));
        }

        if (empty($lastname)) {
            $data_missing['lname'] = "Missing Last Name";
        } else {
            $lastname = trim(Sanitize($lastname));
        }

        if (empty($email)) {
            $data_missing['email'] = "Missing email Address";
        } else {
            $email = trim(Sanitize($email));
        }

        if (empty($phone)) {
            $data_missing['phone'] = "Phone Number";
        } else {
            $phone = trim(Sanitize($phone));
        }

        if (empty($password)) {
            $data_missing['pass'] = "Missing Password";
        } else {
            $password = trim(Sanitize($password));
        }

        if (empty($password1)) {
            $data_missing['confpass'] = "Missing Confirm Password";
        } else {
            if (($formstream['password']) != ($password)) {
                $data_missing['confpass'] = "Password Mismatch";
            } else {
                $password1 = trim(Sanitize($password1));
                $password = sha1($password);
            }
        }

        //   //image adding section
        //   if(empty( $_FILES['image']['name'])){
        //     $data_missing['image'] = "Missing Image";
        //   }else{
        //   $uniqueimagename =time().uniqid(rand());

        //   $target = "../images/" . $uniqueimagename;
        //   $allowtypes = array('jpg', 'png', 'jpeg', 'gif', 'svg');


        //   if (!is_dir("../images")) {
        //       mkdir("../images", 0755);
        //   }

        //   $filename = "";
        //   $tmpfilename = "";

        //       $filename =  $_FILES['image']['tmp_name'];

        //       $tmpfilename = basename( $_FILES['image']['name']);
        // }

        if (empty($data_missing)) {
            $_SESSION['rege'] = "true";
            // AddRegistered($firstname, $lastname, $phone, $email, $username, $password, $job, $twitter, $facebook,  $linkedin, $instagram, $uniqueimagename);
            AddRegistered($firstname, $lastname, $phone, $email, $password);
            //echo '<br>';
            // echo("Everything entered Succesfully");
            //echo '<br>';


            //     $filetype = pathinfo($tmpfilename, PATHINFO_EXTENSION);
            //     if (in_array($filetype, $allowtypes)) {

            //         //upload file to server
            //         if (move_uploaded_file($filename, $target . "." . $filetype)) {

            //         } else {
            //             echo '<br>';
            //             echo 'Something went wrong with the image upload';
            //         }
            //     }
            // } else {
            //     //  echo "<pre>";
            //     // print_r($data_missing);
            //     foreach ($data_missing as $miss) {
            //         echo '<p class="">';
            //         echo "$miss";
            //         echo '</p>';
            //     }
            //     // echo"</pre>";
            //     return false;
            // }
        }
    }
}

function AddRegistered($fname, $lname, $ph, $em, $pass)
{
    //This simply adds the filtered and cleansed data into the database 
    global $db;
    $sql = "INSERT INTO admins(firstname, lastname, phone, email, password) VALUES ('$fname', '$lname', '$ph', '$em', '$pass')";

    if (mysqli_query($db, $sql)) {

        echo "New record created successfully";
        //header("location:login.php");
    } else {
        echo  "<br>" . "Error: " . "<br>" . mysqli_error($db);
    }
    mysqli_close($db);
}

function processLogin($formstream)
{
    //This simply queries the database to see if the users data is really available then sets the users data to a session to show theyve logged in
    extract($formstream);
    global $db;

    if (isset($submit)) {
        // username and password sent from form 

        $myusername = Sanitize($email);
        $mypassword = sha1($password);

        $result = mysqli_query($db, "SELECT * FROM admins WHERE email ='$myusername' AND password = '$mypassword'      ");

        if (mysqli_num_rows($result) > 0 && mysqli_num_rows($result) == 1) {
            $result = $result->fetch_assoc();

            $_SESSION['username'] = ucwords(strtolower($result['firstname'])) . " " . ucwords(strtolower($result['lastname']));
            $_SESSION['firstname'] = $result['firstname'];
            $_SESSION['lastname'] = $result['lastname'];
            //$_SESSION['datejoined'] = $result['datejoined'];
            $_SESSION['email'] = $result['emailaddress'];
            $_SESSION['phone'] = $result['phone'];
            // $_SESSION['profilepic'] = $result['profilepic'];

            $_SESSION['log'] = "true";

            echo "<br>";
            echo 'Logged In';
            echo "<pre>";
            print_r($result);
            header('location:admin.php');
        } else {

            echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Holy guacamole!</strong> Your username or password are incorrect.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>';
        }
    }
}

function processNewCourse($formstream)
{
    extract($formstream);

    if (isset($submit)) {

        $data_missing = [];

        if (empty($code)) {
            $data_missing['Course_code'] = "Missing Course Code";
        } else {
            if (strlen($code) == 6) {
                $code = trim(Sanitize($code));
            } else {
                $data_missing['Course_code'] = "Course code is not 6 characters";
            }
        }

        if (empty($title)) {
            $data_missing['Course_title'] = "Missing Course Title";
        } else {
            $title = trim(Sanitize($title));
        }

        if (empty($level)) {
            $data_missing['Course_level'] = "Missing Course Level";
        } else {

            if($level > 7 || $level < 1){
                $data_missing['Course_level'] = "Invalid Course Level";
            }else{
            $level = trim(Sanitize($level));
            }

        }

        if (empty($credit)) {
            $data_missing['Course_Credit'] = "Missing Course Credit";
        } else {

            if($credit > 8 || $credit < 1){
                $data_missing['Course_Credit'] = "Invalid Course Credit";
            }else{
            $credit = trim(Sanitize($credit));
            }

        }

        if (empty($semester)) {
            $data_missing['Course_semester'] = "Missing Course semester";
        } else {

            if($semester > 2 || $semester < 1){
                $data_missing['Course_semester'] = "Invalid Course semester";
            }else{
            $semester = trim(Sanitize($semester));
            }

        }

        if (empty($faculty)) {
            $data_missing['Course_faculty'] = "Missing Course Faculty";
        } else {
            $faculty = trim(Sanitize($faculty));
        }


        if (empty($data_missing)) {
            $_SESSION['rege'] = "true";
            AddNewCourse($code, $title, $faculty, $credit, $level, $semester);
        }
    }
}

function AddNewCourse($cd, $tit, $fac, $cred, $lvl, $sem)
{
    //This simply adds the filtered and cleansed data into the database 
    global $db;
            $sql = "INSERT INTO courses(code, title, faculty, credit, level, semester ) VALUES ('$cd', '$tit', '$fac', '$cred', '$lvl', '$sem')";

    if (mysqli_query($db, $sql)) {

        echo "Course Saved";
        //header("location:login.php");
    } else {
        echo  "<br>" . "Error: " . "<br>" . mysqli_error($db);
    }
    mysqli_close($db);
}

function loadCourses(){
    global $db;
    $user = "Admin";
    if (!empty($user)) {
        $query = "SELECT id, code, title, faculty, credit, level, semester FROM courses ORDER BY `id` DESC ";
        $response = @mysqli_query($db, $query);
        if ($response) {
            while ($row = mysqli_fetch_array($response)) {
                $_SESSION['course_id'] =  $row['id'];
                //$query2 = "SELECT profilepic FROM users WHERE emailaddress = '$master' ";

               echo '<tr><td>'; 
               echo $row['code'];
               echo '</td>';

               echo  '<td>';
               echo '<a href="edit_course.php?id=';
               echo $row['id'];
               echo '&coursename=';
               echo ucwords(strtolower($row['title']));
               echo '"> ';
               echo ucwords(strtolower($row['title']));
               echo'</a></td>';

               echo '<td>';
               echo ucwords(strtolower($row['faculty']));
               echo '</td>';

               echo '<td>';
               echo $row['level'].'00';
               echo '</td>';

               echo '<td>';
               echo $row['credit'];
               echo '</td>';

               echo '<td>';
               echo $row['semester'];
               echo '</td>';

               echo '<td>';
               echo '<a href="edit_course.php?id=';
               echo $row['id'];
               echo '&coursename=';
               echo ucwords(strtolower($row['title']));
               echo '">';
               echo '<i class="fa fa-edit"></i></a></td>';


               echo '<td><a href="new_exam.php?id=';
               echo $row['id'];
               echo '&coursename=';
               echo ucwords(strtolower($row['title']));
               echo '"><i class="fa fa-plus"></i></a></td>';

               echo '<td>';
               echo '<a href="delete_course.php?id=';
               echo $row['id'];
               echo '"><i class="fa fa-trash"></i></a></td>';
               
               echo '</tr>';
            }
        } else {
            echo 'No Posts Yet';
        }
    }
}

function loadTenyears(){
    global $db;
    $currentYear = date('Y');

    for($i = 0; $i < 10; $i++){

       // $courseId = $_SESSION['course_id'];
       $courseId = $_GET['id'];
        $sql = "select * from q_and_a where (year = '$currentYear' and course_id = '$courseId');";
        $result = mysqli_query($db, $sql);
    
        if(mysqli_num_rows($result) > 0){
    
        }else{
            echo '<option value="1">' ;
            echo $currentYear; 
            echo ' Level</option>';
        }
        $currentYear = $currentYear- 1;
    }

   
// echo '
//     <option value="1">100 Level</option>
//     <option value="2">200 Level</option>
//     <option value="3">300 Level</option>
//     <option value="4">400 Level</option>
//     <option value="5">500 Level</option>
//     <option value="6">600 Level</option>
//     <option value="7">Other</option>
    
//     ';

}
