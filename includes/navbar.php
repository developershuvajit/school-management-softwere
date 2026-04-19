 <div class="nav-header">
     <a href="#" class="brand-logo">
         <img class="logo-abbr" src="../public/images/logo.png" alt="">
         <img class="logo-compact" src="../public/images/logo-text.png" alt="">
         <img class="brand-title" src="../public/images/logo-text.png" alt="">
     </a>
     <div class="nav-control">
         <div class="hamburger">
             <span class="line"></span><span class="line"></span><span class="line"></span>
         </div>
     </div>
 </div>

 <div class="header">
     <div class="header-content">
         <nav class="navbar navbar-expand">
             <div class="collapse navbar-collapse justify-content-between">
                 <div class="header-left">
                     <div class="search_bar dropdown">
                         <span class="search_icon p-3 c-pointer" data-toggle="dropdown">
                             <i class="mdi mdi-magnify"></i>
                         </span>
                         <div class="dropdown-menu p-0 m-0">
                             <form>
                                 <input class="form-control" type="search" placeholder="Search" aria-label="Search">
                             </form>
                         </div>
                     </div>
                 </div>

                 <ul class="navbar-nav header-right">

                     <li class="nav-item dropdown header-profile">
                         <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                             <i class="mdi mdi-account"></i>
                         </a>
                         <div class="dropdown-menu dropdown-menu-right">
                             <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == "super_admin") :  ?>
                                 <a href="../dashboard/profile.php" class="dropdown-item">
                                     <i class="icon-user"></i>
                                     <span class="ml-2">Profile </span>
                                 </a>
                             <?php endif; ?>
                             <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == "parent") :  ?>
                                 <a href="../parents_portal/reset-password.php" class="dropdown-item">
                                     <i class="icon-user"></i>
                                     <span class="ml-2">Reset Password </span>
                                 </a>
                             <?php endif; ?>
                             <a href="../actions/logout.php" class="dropdown-item">
                                 <i class="icon-key"></i>
                                 <span class="ml-2">Logout </span>
                             </a>
                         </div>
                     </li>
                 </ul>
             </div>
         </nav>
     </div>
 </div>