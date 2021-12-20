<?php 
  session_start();

  if (!isset($_SESSION['logged']) || !$_SESSION['logged']){//Check whether user is logged
    echo "<script>alert('Please Login');</script>";
    header("Refresh:0, url=index.php");
  }
  
  $showReport = FALSE;
  $showReportForm = TRUE;
  
  use Phppot\DataSource;
  require_once './import-excel/DataSource.php';
  $db = new DataSource();
  $conn = $db->getConnection();
  
  $tbl_name = "tbl_info";

  if (isset($_POST['batch_id'])){
    $batch_id = mysqli_real_escape_string($conn, $_POST['batch_id']);
  }

  if(!isset($batch_id)){
    $batch_id = $_SESSION['batch_id'];
  }else{
    $_SESSION['batch_id'] = $batch_id;
  }

  $tblHead = ''; 
  $tblRows = '';
  $sql = "SELECT * FROM $tbl_name WHERE batch_id='$batch_id'";

  $ctr = -1;
  if($res = mysqli_query($conn, $sql)){
    while ($row = mysqli_fetch_assoc($res)){
      $id = $row['id'];
      $name = $row['name'];
      $date = $row['date created'];
      $actualDate = $row['actual date'];

      if($ctr < 0){
        $tblHead .= "<th>ID</th>";
        $tblHead .= "<th>Name</th>";
        $tblHead .= "<th>Date</th>";
        $tblHead .= "<th>Actual Date</th>";
      }

      $tblRows .= "
                    <tr>
                      <td>$name</td>
                      <td>$id</td>
                      <td>$date</td>
                      <td>$actualDate</td>
                  ";

      foreach(array_keys($row) as $item){
        if($item == 'id' || $item == 'name' || $item == 'date' || $item == 'actual date' || $item == 's/n' || $item == 'batch_id'){
          continue;
        }

        if($ctr < 0){
          $tblHead .= "<th>".ucfirst($item)."</th>";
        }

        $tblRows .= "<td>".$row[$item];
      }
      
      $tblRows .= "
                    <td>
                      <form action='' method='POST'>
                        <input type='hidden' name='id' value='$id'>
                        <button name='delete-single' class='btn btn-danger' onclick='return confirm(`Are You Sure You Want To Delete Row With ID $id`)'>DELETE</button>
                      </form>
                    </td>
                  ";
      $tblRows .= "</tr>";  

      $ctr++;
    }
    $tblHead .= "<th>Action</th>";
  }

  if(isset($_POST['delete-single'])){//Delete a single row
    $id = $conn ->real_escape_string($_POST['id']);

    $sql = "DELETE FROM $tbl_name WHERE id='$id'";
    if($conn->query($sql)){
      echo "<script>alert('Row Deleted Successfully');</script>";
    }else{
      echo "<script>alert('Error Encountered!');</script>";
    }
  }  
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - Excel To SQL</title>
  <link rel="stylesheet" href="./src/bootstrap.min.css">
  <script src="./src/bootstrap.bundle.min.js"></script>
  <style>  
    @media (min-width: 992px){
        .navbar-nav a {
            margin-left : 1em;
            margin-right : 1em;
        }
    }
  </style>
</head>
<body>
  <div class="container-fluid m-0 p-0"> 
    <!-- <h1 class="text-light m-0 py-3 text-center bg-dark">Batch </h1> -->
    <!-- Navbar starts -->
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
      <!-- Navbar content -->
      <div class="container">
        <a class="navbar-brand" style="margin-right: 12rem;" href="./index.php">Excel To SQL</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
          <div class="navbar-nav px-4">
            <a class="nav-link active" aria-current="page" href="./dashboard.php">Batch <?php echo $batch_id; ?></a>
            <a class="nav-link" href="./dashboard.php">Dashboard</a>
            <a class="nav-link" href="./import-excel/">Import Excel</a>
            <a class="nav-link" href="./history.php">Upload History</a>
            <a class="nav-link" href="./reports.php">Generate Reports</a>
            <a class="nav-link" href="./logout.php">Log Out</a>
          </div>
        </div>
      </div>
    </nav>
    <!-- Navbar ends -->

    <div class="row mt-3" id="report">
      <div class="col-md-10 offset-md-1">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead class="table-dark">
              <tr><?php echo $tblHead; ?></tr>
            </thead>

            <tbody>
              <?php echo isset($tblRows) ? $tblRows : '' ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <footer class="text-light m-0 py-3 text-center bg-dark">Copyright 2021 &copy;Dumos Technologies</footer>
  </div>
</body>
</html>