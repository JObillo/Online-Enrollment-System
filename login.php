<?php 

include 'database.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // Prepare the SQL statement using MySQLi
   $stmt = $connection->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
   $stmt->bind_param("ss", $email, $pass); // Bind parameters: "ss" means two strings
   $stmt->execute(); // Execute the query
   $result = $stmt->get_result(); // Fetch the result set

   if($result->num_rows > 0){
      $row = $result->fetch_assoc(); // Fetch the row as an associative array

      if($row['user_type'] == 'admin'){

         $_SESSION['user_id'] = $row['user_id'];
         header('location:ADMIN/admin-page.php');

      } elseif($row['user_type'] == 'dean') {

         $_SESSION['user_id'] = $row['user_id'];
         header('location:DEAN/dean-page.php');   

      } elseif($row['user_type'] == 'student') {

         $_SESSION['user_id'] = $row['user_id'];
         header('location:STUDENTS/student-page.php');   

      } else {
         $message[] = 'no user found!';
      }
      
   } else {
      $message[] = 'incorrect email or password!';
   }

   // Close the statement
   $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/login.css">

   <style>
       /* Styling the error message */
       .message {
           background-color: #f8d7da;
           color: #721c24;
           padding: 15px 20px;
           border: 1px solid #f5c6cb;
           border-radius: 5px;
           margin: 15px 0;
           text-align: center;
           font-size: 1rem;
           position: relative;
       }
       .message i {
           position: absolute;
           right: 10px;
           top: 50%;
           transform: translateY(-50%);
           cursor: pointer;
           color: #721c24;
       }
   </style>
</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>
   
   <section class="form-container">
   <div class="login">
      <div class="logo-container">
         <img src="IMAGES/logo(1).png" alt="School Logo" class="logo">
      </div>

      <form action="" method="post" enctype="multipart/form-data">
         <h3>Log In</h3>
         <input type="email" required placeholder="Enter your email" class="box" name="email">
         <input type="password" required placeholder="Enter your password" class="box" name="pass" id="password">
         <p>Don't have an account? <a href="register.php">Register now</a></p>
         <input type="submit" value="Login Now" class="btn" name="submit">
      </form>
   </div>

   <div class="banner">
    <div class="name">
        <span>J</span>
        <span>o</span>
        <span>i</span>
        <span>n</span>
        <span>&nbsp;</span>
        <span>M</span>
        <span>o</span>
        <span>n</span>
        <span>a</span>
        <span>r</span>
        <span>c</span>
        <span>h</span>
        <span>&nbsp;</span>
        <span>C</span>
        <span>o</span>
        <span>l</span>
        <span>l</span>
        <span>e</span>
        <span>g</span>
        <span>e</span>
    </div>
    <p class="slogan">"Inspiring Excellence, Achieving Greatness"</p>
</div>

</section>

</body>
</html>
