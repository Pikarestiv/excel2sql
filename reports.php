<?php 
  session_start();

  if (!isset($_SESSION['logged']) || !$_SESSION['logged']){
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

  function loadNames($conn, $tbl_name){
    $sql = "SELECT DISTINCT id, `name` FROM $tbl_name";
    $nameDataListOptions = "";

    if($result = mysqli_query($conn, $sql)){
      while($row = mysqli_fetch_assoc($result)){
        $namesForDatalist = $row['name'];
        $nameDataListOptions .= "<option value='$namesForDatalist'>";
      }
    }
    return $nameDataListOptions;
  }

  function loadCols($conn, $tbl_name){
    //SQL To Query DB For Column Names
    $schemaSQL = "
      SELECT `COLUMN_NAME` 
      FROM `INFORMATION_SCHEMA`.`COLUMNS` 
      WHERE `TABLE_SCHEMA`='import_excel' 
      AND `TABLE_NAME`='$tbl_name';
    ";
    $colDatalistOptions = "";

    //Query DB for column names
    if($result = mysqli_query($conn, $schemaSQL)){
      $i = 0;
      while($row = mysqli_fetch_array($result)){
        if($i < 3){
          $i++;
          continue;
        }
        $colsForDatalist = $row[0];
        $colDatalistOptions .= "<option value='$colsForDatalist'>";
      }
    }
    return $colDatalistOptions;
  }

  if(isset($_POST['genReport'])){
    $showReportForm = FALSE;
    $showReport = TRUE;

    $name = $_POST['name'];
    $col = $_POST['col'];
    $col = explode(',', $col);
    $col4SQL = [];
    $tblHead = '';
    
    foreach($col as $item){
      $col4SQL[] = "`" . $item . "`";
    }


    if(isset($name)){
      // $sql = "SELECT `id`, `date created`, `$col` FROM $tbl_name WHERE id='$id'";
      $sql = "SELECT `id`, `name`, `actual date`, `date created`, " . implode(",", $col4SQL) . " FROM $tbl_name WHERE name='$name'";
      $tblHeadArr = [];//Array of row headers
      $tblHead = "";//Table head tag from row headers
      $tblRows = "";

      if($result = mysqli_query($conn, $sql)){
        while($row = mysqli_fetch_assoc($result)){ 
          if(count($tblHeadArr) < 1){
            $tblHeadArr = array_keys($row);

            foreach($tblHeadArr as $item){
              $tblHead .= "<th>".ucfirst($item)."</th>";
            }
          }

          $tblRows .= "<tr>";
          foreach($row as $col){
            $tblRows .= "
              <td>".$col."</td>
            ";
          }
          $tblRows .= "<tr>";
        }
      }
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
  <link rel="stylesheet" href="./src/jquery.flexdatalist.min.css">
  <script src="./src/jquery-3.6.0.min.js"></script>
  <style>
    #col-flexdatalist:focus{
      outline: none !important;
      box-shadow: none !important;
      padding: 3px;
    }
  </style>
  <script type="text/javascript" src="./src/xlsx.full.min.js"></script>
  <script>
    function exportToExcel(type, fn, dl) {
      var elt = document.getElementById('report-table');
      var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
      let currDate = Date.now();

       return dl ?
         XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
         XLSX.writeFile(wb, fn || ('Report-' + currDate + '.' + (type || 'xlsx')));
    }
  </script>
</head>

<body>
  <div class="container-fluid m-0 p-0"> 
    <?php $generateReports = 'active'; require_once('header.php') ?>
    <div class="row d-flex flex-column justify-content-center" style="height: 80vh; display: <?php echo $showReportForm ? 'block' : 'none !important' ?>">
      <div class="report-div col-md-4 col-sm-8 offset-md-4 offset-sm-2 bg-light p-4">
        <form action="./reports.php" method="POST" class="login">
          <div id="response" class="mb-4 <?php if (!empty($type)) {echo $type . " display-block";} ?>">
            <?php if (!empty($message)) {echo $message;} ?>
          </div>
          <!-- select id to use for the report -->
          <div class="form-group mb-3">
            <label for="name" class="form-label">Names</label>
            <input autocomplete="off" name="name" required class="form-control" list="name-list" id="name" placeholder="Type To Search...">
            <datalist name="name-list" required id="name-list">
              <?php echo loadNames($conn, $tbl_name); ?>
            </datalist>
          </div>

          <!-- Select columns to include in the report -->
          <div class="form-group">
            <label for="col" class="form-label">Columns</label>
            <input 
              type="text" 
              autocomplete="off" 
              name="col"  
              required 
              class="flexdatalist form-control" 
              list="column-list" 
              id="col" 
              placeholder="Search Columns..."
              data-min-length='0'
              multiple=''
              data-selection-required='1'
            >
            <datalist name="column-list" required id="column-list">
              <?php echo loadCols($conn, $tbl_name); ?>
            </datalist>
          </div>

          <button class="btn btn-dark form-control p-2 my-3" id="" name="genReport" type="submit">Generate Report</button>        
        </form>
      </div>
    </div>
    
    <div class="row mt-3" id="report" style="display: <?php echo $showReport ? 'block' : 'none' ?>">
      <div class="col-md-10 offset-md-1">
        <div class="row mb-2">
          <div class="col-md-10"></div>
          <div class="col-md-2">
            <button onclick="exportToExcel('xlsx')" class='btn btn-danger'>Export As Spreadsheet</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-striped" id="report-table">
            <thead class="table-dark">
              <tr>
                <?php echo isset($tblHead) ?  $tblHead : '' ?>
              </tr>
            </thead>
            <tbody>
              <?php echo isset($tblRows) ? $tblRows : '' ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <footer class="text-light m-0 py-3 text-center bg-dark" style="position: fixed; bottom: 0; left: 0; right: 0;">Copyright 2021 &copy;Dumos Technologies</footer> 
  </div>
  <script src="./src/jquery.flexdatalist.js"></script>
</body>
</html>