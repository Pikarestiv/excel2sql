<?php
session_start();
  if (!isset($_SESSION['logged']) || !$_SESSION['logged']){
    echo "<script>alert('Please Login');</script>";
    header("Refresh:0, url=index.php");
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Excel To SQL</title>
  <link rel="stylesheet" href="./src/bootstrap.min.css">
  <script src="./src/bootstrap.bundle.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
    }
    .menu-link:hover{
      margin: 1px 0;
      font-size: 1.2em;
    }

  </style>
</head>
<body>
  <div class="container-fluid m-0 p-0"> 
    
    <?php $dashboard = 'active'; require_once('header.php') ?>
    <div class="row d-flex flex-column justify-content-center" style="height: 80vh">
      <div class="login-div col-md-4 col-sm-8 offset-md-4 offset-sm-2 bg-light p-4">        
          <div class="row bg-dark mb-3 p-2 rounded">
            <a class="menu-link text-decoration-none text-center text-light" href="./import-excel">Upload Spreadsheet</a>
          </div>

          <div class="row bg-dark mb-3 p-2 rounded">
            <a class="menu-link text-decoration-none text-center text-light" href="./history.php">View History Of Uploads</a>
          </div>

          <div class="row bg-dark mb-3 p-2 rounded">
            <a class="menu-link text-decoration-none text-center text-light" href="./reports.php">View Reports</a>
          </div>
          
          <div class="row bg-dark mb-3 p-2 rounded">
            <a class="menu-link text-decoration-none text-center text-light" href="./logout.php">Log Out</a>
          </div>

      </div>
    </div>
    <footer class="text-light m-0 py-3 text-center bg-dark" style="position: fixed; bottom: 0; left: 0; right: 0;">Copyright 2021 &copy;Dumos Technologies</footer>
  </div>
</body>
</html>