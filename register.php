<?php 
include 'database.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   // Set the default image filename
   $default_image = 'default_image.jpg';

   // Prepare the SQL statement using MySQLi
   $stmt = $connection->prepare("SELECT * FROM `users` WHERE email = ?");
   $stmt->bind_param("s", $email); // "s" for string
   $stmt->execute(); // Execute the query
   $result = $stmt->get_result(); // Get the result set

   if($result->num_rows > 0){
      $message[] = 'user already exists!';
   } else {
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      } else {
         // Insert user with default image
         $insert_stmt = $connection->prepare("INSERT INTO `users`(full_name, email, password, images) VALUES(?,?,?,?)");
         $insert_stmt->bind_param("ssss", $name, $email, $cpass, $default_image); // "ssss" means four strings
         $insert_stmt->execute();

         if($insert_stmt->affected_rows > 0){
            $message[] = 'registered successfully!';
            header('location:login.php');
         } else {
            $message[] = 'error in registration!';
         }

         // Close the insert statement
         $insert_stmt->close();
      }
   }

   // Close the select statement
   $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="CSS/register.css">

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
   <div class="form-wrapper">
      <form action="" method="post">
         <h3>Register Now</h3>
         <input type="text" required placeholder="Enter your full name:" class="box" name="name">
         <input type="email" required placeholder="Enter your email:" class="box" name="email">
         <input type="password" required placeholder="Enter your password:" class="box" name="pass">
         <input type="password" required placeholder="Confirm your password:" class="box" name="cpass">
         <p>Already have an account? <a href="login.php">Login now</a></p>
         <input type="submit" value="Register Now" class="btn" name="submit">
      </form>
   </div>

   <div class="banner">
      <img src="IMAGES/logo(1).png" alt="School Logo" class="logo">
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