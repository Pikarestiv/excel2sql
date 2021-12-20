<?php
//Initializing 
session_start();

if (!isset($_SESSION['logged']) || !$_SESSION['logged']){
	echo "<script>alert('Please Login');</script>";
	header("Refresh:0, url=index.php");
}

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
require_once 'DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
require_once('./vendor/autoload.php');
$allowedFileType = [
	'application/vnd.ms-excel',
	'text/xls',
	'text/xlsx',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$firstDataRow = '9' ;

if(isset($_SESSION['firstDataRow'])){
	$firstDataRow = $_SESSION['firstDataRow'];
}

$tbl_name = "tbl_info";


//First Submit
if (isset($_POST["import"])) {
	if (in_array($_FILES["file"]["type"], $allowedFileType)) {

		$_SESSION['import-date'] = $_POST['import-date'];
		$targetPath = 'uploads/' . $_FILES['file']['name'];
		move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
		$Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();//Init Php Reader

		$spreadSheet = $Reader->load($targetPath);
		$excelSheet = $spreadSheet->getActiveSheet();
		$spreadSheetAry = $excelSheet->toArray();

		$highestRow = $excelSheet->getHighestRow();
		$highestColumn = $excelSheet->getHighestColumn();
		$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

		for($rw = 1; $rw <= $highestRow; $rw++){//For loop to set data header row
			$val = $excelSheet->getCellByColumnAndRow(1, $rw);
			if(strtolower($val) == 'id'){
				$firstDataRow = $rw;
				$_SESSION['firstDataRow'] = $firstDataRow;
				break;
			}
		}

		if ($excelSheet->getCellByColumnAndRow(1, $firstDataRow)->getValue() == "ID") {

			$columnChoices = "<h4 class='mt-4'>Columns Found In The worksheet</h4>";
			
			$choicesArr = [];

			for ($firstDataRow, $i = 1; $i <= $highestColumnIndex; ++$i) {
				$val = $excelSheet->getCellByColumnAndRow($i, $firstDataRow);
				$val = strtolower($val);
				if (empty($val) || is_null($val) || $val == "") continue;

				$checkedBool = 'checked';
				if(in_array($val, $choicesArr)){
					$checkedBool = 'unchecked';
				}

				$columnChoices .=
					"
								<li class='li-choices' id='li-$i'>
									<input type='checkbox' name='choices[]' $checkedBool class='column' id='checkbox-$i' value='$val'>
									<label for id='checkbox-$i'>$val</label>
								</li>
							";
				$choicesArr[] = $val;
				$val = "";
			}
			$columnChoices .= "<button id='submitBtn' class='btn btn-dark mb-5' name='submitBtn'>Upload Selected Columns</buttons>";

			// Preparing for posting
			$sheet = base64_encode(serialize($excelSheet));

		}else{
			$type = "error";
			$message = "Incorrect excel sheet format or contents";
		}

		}else {
			$type = "error";
			$message = "Invalid File Type. Upload Excel File.";
		}
	} 
	//End First Submit


//Second Submit
if(isset($_POST["submitBtn"])){
	$sheetData = unserialize(base64_decode($_POST["sheetData"]));
	$highestRow = $sheetData->getHighestRow();
	$highestColumn = $sheetData->getHighestColumn();
	$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);//Get Numerical Column Index From Alphabetical Column Index
	$importDate = $_SESSION['import-date'];

	// Prepare Batch Number
	$batch_id ='a2s5d7f1g0h3j2k6l4q8w6e3r9t2y1u5i9o7p4z8x0c1v2b3n4m567890';
	$batch_id = str_shuffle($batch_id);
	$batch_id = substr($batch_id, 0, 10);

	$schemaSQL = "
		SELECT `COLUMN_NAME` 
		FROM `INFORMATION_SCHEMA`.`COLUMNS` 
		WHERE `TABLE_SCHEMA`='import_excel' 
				AND `TABLE_NAME`='$tbl_name';
	";


	$res = mysqli_query($conn, $schemaSQL);//Query DB for column names
	$colNames = [];
	while($row = mysqli_fetch_array($res)){
		$colNames[] = strtolower($row[0]);
	}

	$selectedChoices = $_POST['choices'];	//Choices chosen at frontend
	$selectedChoices = array_unique($selectedChoices);
	$choicesNotInDB = array_diff($selectedChoices, $colNames);//Choices chosen but not in db

	foreach($choicesNotInDB as $newCol) {//Create columns for selected choices which are not already in db
		$sql = "
						ALTER TABLE $tbl_name
						ADD `$newCol` varchar(100) NOT NULL;
		";

		$newColErrors = '';
		if(mysqli_query($conn, $sql)){
			// echo "<script>alert('$newCol added successfully to DB Table $tbl_name');</script>";
		}else {
			$newColErrors .= 'Error Encountered! Unable to add $newCol to DB Table $tbl_name <br />';
			$newColErrors .= mysqli_error($conn);
		}
	}
	
	//Loop through cells and upload to db
	for ($row = $firstDataRow + 1; $row <= $highestRow; ++$row) {
		$stmtSQL = '';
		$col4SQL = [];
		$val4SQL = [];
		$headerArr = [];//Initialize array to track if header has been already met

		for($col = 1; $col <= $highestColumnIndex; $col++){
			$headerName = strtolower($sheetData->getCellByColumnAndRow($col, $firstDataRow));
			if(in_array($headerName, $headerArr)){
				continue; //if header name is duplicated, skip the other instances
			}

			$headerCellValue = $sheetData->getCellByColumnAndRow($col, $row);

			if(in_array($headerName, $selectedChoices)){
				$col4SQL[] = "`$headerName`";
				$val4SQL[] = "'$headerCellValue'";
			}else{
				$type = "error";
				$message = "Columns Not Existing In Database";
			}

			$headerArr[] = $headerName; //Indicate header has been met 
		}
		$currDate = date("Y-m-d");
		$stmtSQL = "INSERT INTO $tbl_name (`date created`, `actual date`, `batch_id`, ".implode(",", $col4SQL). ") VALUES ('$importDate', '$currDate', '$batch_id'," . implode(", ", $val4SQL) . ");";
		
		if (mysqli_query($conn, $stmtSQL)) {
				$type = "success";
				$message = "Excel Data Imported Successfully!";
		} else {
				$type = "error";
				$message = "Problem in Importing Excel Data<br />" . mysqli_error($conn);
		}
	}
}
//End Second Submit
?>




<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" href="./src/styles/style.css">
	<link rel="stylesheet" href="../src/bootstrap.min.css">
  <script src="../src/bootstrap.bundle.min.js"></script>
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
    <!-- <h1 class="text-light m-0 py-3 text-center bg-dark">Dashboard</h1> -->
		<!-- Navbar starts -->
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
      <!-- Navbar content -->
      <div class="container">
        <a class="navbar-brand" style="margin-right: 12rem;" href="../index.php">Excel To SQL</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
          <div class="navbar-nav px-4">
            <a class="nav-link active" aria-current="page" href="../import-excel/">Import Excel</a>
            <a class="nav-link" href="../dashboard.php">Dashboard</a>
            <a class="nav-link" href="../history.php">Upload History</a>
            <a class="nav-link" href="../reports.php">Generate Reports</a>
            <a class="nav-link" href="../logout.php">Log Out</a>
          </div>
        </div>
      </div>
    </nav>
    <!-- Navbar ends -->

    <div class="row d-flex flex-column justify-content-center" style="padding-top: 9rem">

			<div class="outer-container col-md-4 offset-md-4 col-sm-8 offset-sm-2" style="display: <?php echo isset($_POST["import"]) ? 'none' : 'block' ;?>">	

				<div id="response" class="<?php if (!empty($type)) {echo $type . " display-block";} ?>">
					<?php if (!empty($message)) {echo $message;} ?>
				</div>

				<form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
					<div class="form-group mb-2">
						<label class="mb-2">Choose Excel File</label>
						<input required  class="form-control" type="file" name="file" id="file" accept=".xls,.xlsx">
					</div>

					<div class="form-group mb-2">
						<label for="import-date" class="mb-2">Select Date: </label> 
						<input type="date" class="form-control" name="import-date" id="import-date" value=<?php echo date("Y-m-d"); ?>>
					</div>

						<button type="submit" id="submit" name="import" class="btn btn-dark btn-submit form-control mt-2">Import</button>
				</form>

			</div>

			<!-- <div class="outer-container col-md-4 offset-md-4 col-sm-8 offset-sm-2"> -->
			<form id="choices-form" class="col-md-4 offset-md-4 col-sm-8 offset-sm-2" method="post" action="./index.php">
				<input type="hidden" name="sheetData" value="<?php echo $sheet ?>" />
				<input type="hidden" name="mainRow" value="<?php echo $mainRow ?>" />
				<ul class="column-choices" class="outer-container col-md-4 offset-md-4 col-sm-8 offset-sm-2" style="margin-bottom: 16px;">
					<!-- Dynamically print choice columns with checkboxes -->
					<?php if (!empty($columnChoices)) {
						echo $columnChoices;
					} 
					?>
				</ul>
			</form>
			<!-- </div> -->
		</div>
		
	</div>

    <footer class="text-light mt-5 py-3 text-center bg-dark fixed-bottom">Copyright 2021 &copy;Dumos Technologies</footer>
</body>

</html>