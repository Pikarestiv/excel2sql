<?php 
  session_start();
  use Phppot\DataSource;
  require_once './import-excel/DataSource.php';
  $db = new DataSource();
  $conn = $db->getConnection();
  if (!isset($_SESSION['logged']) || !$_SESSION['logged']){
    echo "<script>alert('Please Login');</script>";
    header("Refresh:0, url=index.php");
  }
  $tbl_name = "tbl_info";

  if(isset($_POST['delete-batch'])){//Delete whole batch
    $batch_id = $conn->real_escape_string($_POST['batch_id']);
    $sql = "DELETE FROM $tbl_name WHERE batch_id='$batch_id'";
    if($conn->query($sql)){
      echo "<script>alert('Batch Deleted Successfully');</script>";
      header("Location: history.php");
    }else{
      echo "<script>alert('Error Encountered, Could Not Delete Batch!');</script>";
      header("Location: history.php");
    }
  }

  function loadBatches($conn, $tbl_name){
    $sql = "SELECT *
    FROM $tbl_name
    WHERE `s/n` IN
    (
        SELECT MAX(`s/n`)
        FROM $tbl_name
        GROUP BY batch_id
    );";

    if ($result = mysqli_query($conn, $sql)){
      $tblRows = '';
      while($batchRows = mysqli_fetch_assoc($result)){
        $batch_id = $batchRows['batch_id'];
        $numRowsRes = mysqli_query($conn, "SELECT count(batch_id) as cnt FROM $tbl_name WHERE batch_id='$batch_id'");
        $rowCnt = mysqli_fetch_assoc($numRowsRes);

        $date = $batchRows['date created'];
        $actualDate = $batchRows['actual date'];
        $number_of_rows = $rowCnt['cnt'];
        $deleteSingle = '
                          <form action="./batch_view.php" class="d-inline" method="POST">
                            <input type="hidden" name="batch_id" value="' . $batch_id . '">
                            <button class="btn btn-primary"><i class="fas fa-eye"></i></button>
                          </form>
                        ';

        $tblRows .= "
          <tr>
            <td>$batch_id</td>
            <td>$date</td>
            <td>$actualDate</td>
            <td>$number_of_rows</td>
            <td>$deleteSingle
              &nbsp;
              <form action='' class='d-inline delete-row' method='POST'>
                <input type='hidden' name='batch_id' value='$batch_id'>
                <button name='delete-batch' class='btn btn-danger del-btn' onclick='return confirm(`Are You Sure You Want To Delete Batch $batch_id?`);'><i class='fas fa-times-circle'></i></button> 
              </form>
            </td>
          </tr>
        ";
      }
      return $tblRows;
    }

  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload History</title>
  <link rel="stylesheet" href="./src/bootstrap.min.css">
  <link rel="stylesheet" href="./src/fontawesome/css/all.css">
  <script src="./src/bootstrap.bundle.min.js"></script>
</head>
<body>
  
  <div class="container-fluid m-0 p-0"> 
    <?php $history = 'active'; require_once('header.php') ?>
    <div class="row mt-3" id="report" style="display: <?php echo $showReport ? 'block' : 'none' ?>">
      <div class="col-md-10 offset-md-1">
        
      </div>
    </div>

    <div class="row mt-3" id="report" style="display: <?php echo $showReport ? 'block' : 'none' ?>">
      <div class="col-md-10 offset-md-1">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead class="table-dark">
              <tr>
                <th>Batch ID</th>
                <th>Date Uploaded</th>
                <th>Actual Date Uploaded</th>
                <th>Number Of Rows In Upload</th>
                <th>Action</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php echo loadBatches($conn, $tbl_name); ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
  </div>
  
  <footer class="text-light m-0 py-3 text-center bg-dark" style="position: fixed; bottom:0; left:0; right:0;">Copyright 2021 &copy;Dumos Technologies</footer>
</body>
</html>