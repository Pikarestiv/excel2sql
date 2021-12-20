<style>  
  @media (min-width: 992px){
        .navbar-nav a {
            margin-left : 1em;
            margin-right : 1em;
        }
    }
</style>

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
            <a class="nav-link <?php echo $dashboard; ?>" aria-current="page" href="./dashboard.php">Dashboard</a>
            <a class="nav-link <?php echo $importExcel; ?>" href="./import-excel/">Import Excel</a>
            <a class="nav-link <?php echo $history; ?>" href="./history.php">Upload History</a>
            <a class="nav-link <?php echo $generateReports; ?>" href="./reports.php">Generate Reports</a>
            <a class="nav-link" href="./logout.php">Log Out</a>
          </div>
        </div>
      </div>
    </nav>
    <!-- Navbar ends -->