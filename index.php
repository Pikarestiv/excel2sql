<?php 
session_start();
use Phppot\DataSource;
require_once './import-excel/DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
$default_password = '123';

  if (isset($_POST['password'])){
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    if($password == $default_password){
      $_SESSION['logged'] = TRUE;
      $type = "success";
      $message = "Logging In...";
      // header('Location: dashboard.php');
      header( "Refresh:3; url=dashboard.php");
    }else{
      $type = "error";
      $message = "Incorrect Password";
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Excel To SQL</title>
	<link rel="stylesheet" href="./src/bootstrap.min.css">
  <script src="./src/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="./src/fontawesome/css/all.css">
  <style>
    * {
      box-sizing: border-box;
    }
    body{
      background-color: #7d7a7a; 
    }
    #response {
      padding: 10px;
      margin-top: 10px;
      border-radius: 2px;
      display: none;
    }
    #response.display-block {
      display: block;
    }
    .error {
      background: #fbcfcf;
      border: #f3c6c7 1px solid;
    }
    .success {
      background: #c7efd9;
      border: #bbe2cd 1px solid;
    }
    .login-div{
      height: 300px;
    }
  </style>
</head>
<body>
  <div class="container d-flex flex-column justify-content-center" style="height: 100vh">
    
    <div class="login-div col-md-4 col-sm-8 offset-md-4 offset-sm-2 bg-light p-4">
      <h1 class="text-dark text-center pb-4">Excel To SQL</h1>
      <form action="./index.php" method="POST" class="login mx-4">
        <div id="response" class="mb-4 <?php if (!empty($type)) {echo $type . " display-block";} ?>">
          <?php if (!empty($message)) {echo $message;} ?>
        </div>

        <div class="input-group mt-4 mb-3">
          <span class="input-group-text" id="password"><i class="fas fa-lock text-secondary"></i></span>
          <input required="required" 
            name="password" 
            type="password" 
            class="form-control <?php if (!empty($type)) {echo $type=="success"?"border-success":"border-danger" ;} ?>" 
            placeholder="Enter Password" 
            aria-label="Password" 
            aria-describedby="password" 
          />
        </div>
        
        <button class="login form-control btn btn-dark " type="submit">LOG IN</button>
      </form>

    </div>
  </div>
</body>
</html>