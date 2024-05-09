
<?php
// Get the root URL of the website
$rootUrl = (isset($_SERVER['HTTPS']) ? "http://localhost/student/" : "http://") . $_SERVER['HTTP_HOST'];

// If your application is in a subfolder, append the folder name to the root URL
// For example, if your app is located in the 'myapp' folder, uncomment the line below and replace 'myapp' with the actual folder name
// $rootUrl .= '/myapp';
?>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href=" # " target="_blank">
        <img src="./assets/img/logo99n.webp" class="logoimg navbar-brand-img h-100" alt="">
        <!-- <span class="ms-1 font-weight-bold">99notes</span> -->
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto mysidebar " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link  active" href="<?php echo $rootUrl; ?>/student/student_dashboard.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
             <i class="fa fa-home text-white text-lg"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link  " href="<?php echo $rootUrl; ?>/student/preresult.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-hand-o-right text-dark fs-5" aria-hidden="true"></i>
            </div>
            <span class="nav-link-text ms-1">Pre Results</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link  " href="<?php echo $rootUrl; ?>/student/mainsresult.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-hand-o-right text-dark fs-5" aria-hidden="true"></i>
            </div>
            <span class="nav-link-text ms-1">Mains Results</span>
          </a>
        </li>
      </ul>
    </div>
  
  </aside>