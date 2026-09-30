<?php 
//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

//SLS2021004 bhaarath
if($_SESSION["ClientID"]=='SLS20210044dd'){ //SLS20210044
?>



<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
			
               <!-- <a href="index.html" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="<?php echo $BaseDist ?>assets/images/logo-sm-dark.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?php echo $BaseDist ?>assets/images/logo-dark.png" alt="" height="20">
                    </span>
                </a> -->
<br>
                <a href="#" class="logo logo-light">
                    <span class="logo-sm">
                        <img class="rounded-circle header-profile-user" height="40" src="<?php echo $AppLogo ?>"
                        alt="Header Avatar">
                    </span>
                    <span class="logo-lg ">
                        <img class="rounded-circle header-profile-user" height="40" src="<?php echo $AppLogo ?>"
                        alt="Header Avatar">
						
                    </span>
					
					
					
                </a>
				
			 
           
            </div>
			
				

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="ri-menu-2-line align-middle"></i>
            </button>
			
			

            <!-- App Search-->
         <!--  <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <label type="text" class="form-control" placeholder="S-erp">S-ERP</label>
					
                    
                </div>
            </form>  -->

        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ml-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ri-search-line"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                    aria-labelledby="page-header-search-dropdown">
                    
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <div class="dropdown d-none d-sm-inline-block">
		
		
                <button type="button" class="btn header-item waves-effect"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <label type="text" class="form-control" placeholder="S-erp"><?php echo $ShortName ?></label>
                </button>
            <!--  <div class="dropdown-menu dropdown-menu-right">
                    
                    <!-- item-->
                   <!--  <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="<?php echo $BaseDist ?>assets/images/flags/spain.jpg" alt="user-image" class="mr-1" height="12"> <span class="align-middle">Spanish</span>
                    </a> 
                </div>-->
            </div>


            <div class="dropdown d-none d-lg-inline-block ml-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>

        <!--    <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ri-notification-3-line"></i>
                    <span class="noti-dot"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0"> Notifications </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small"> View All</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs mr-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="ri-shopping-cart-line"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Comming Soon</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1">Comming Soon</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        
                    </div>
                    <div class="p-2 border-top">
                        <a class="btn btn-sm btn-link font-size-14 btn-block text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle mr-1"></i> View More..
                        </a>
                    </div>
                </div>
            </div>  -->

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="<?php echo $AppLogo ?>"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ml-1"><?php echo  $_SESSION["UserName"] ;?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <!-- item-->
                   <!-- <a class="dropdown-item" href="#">
					<!--<span class="badge badge-success float-right mt-1">11</span>-->
					<!--<i class="ri-user-line align-middle mr-1"></i> Profile</a> -->
                   <div class="dropdown-divider"></div>
                    <a class="dropdown-item " href="<?php echo  $BaseUrl.'/ChangePassword' ?>"><i class="ri-user-line align-middle mr-1 text-info"></i> Change Password</a>
                   <div class="dropdown-divider"></div> 
				   <a class="dropdown-item text-danger" href="<?php echo  $BaseUrl.'/auth-login' ?>"><i class="ri-shut-down-line align-middle mr-1 text-danger"></i> Logout</a>
                </div>
            </div>

           <!-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="ri-settings-2-line"></i>
                </button>
            </div>-->
            
        </div>
    </div>
</header>









<?php } else {?>



 <body data-topbar="dark" class="sidebar-enable vertical-collpsed fullscreen-enable" >

        <!-- Begin page -->
       <!-- <div id="layout-wrapper">-->

           <header id="page-topbar">
    <div class="navbar-header" style="display : none" >
        <div class="d-flex" height="50">
            <!-- LOGO -->
            <div class="navbar-brand-box" height="50">
			
              
<br>
              
				
			 
           
            </div>
			
				

          

        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ml-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ri-search-line"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                    aria-labelledby="page-header-search-dropdown">
                    
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <div class="dropdown d-none d-sm-inline-block">
		
		
                <button type="button" class="btn header-item waves-effect"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <label type="text" class="form-control" placeholder="S-erp"><?php echo $ShortName ?></label>
                </button>
            <!--  <div class="dropdown-menu dropdown-menu-right">
                    
                    <!-- item-->
                   <!--  <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="<?php echo $BaseDist ?>assets/images/flags/spain.jpg" alt="user-image" class="mr-1" height="12"> <span class="align-middle">Spanish</span>
                    </a> 
                </div>-->
            </div>


            <div class="dropdown d-none d-lg-inline-block ml-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>


            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="<?php echo $AppLogo ?>"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ml-1"><?php echo  $_SESSION["UserName"] ;?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <!-- item-->
                   <!-- <a class="dropdown-item" href="#">
					<!--<span class="badge badge-success float-right mt-1">11</span>-->
					<!--<i class="ri-user-line align-middle mr-1"></i> Profile</a> -->
                   <div class="dropdown-divider"></div>
                    <a class="dropdown-item " href="<?php echo  $BaseUrl.'/ChangePassword' ?>"><i class="ri-user-line align-middle mr-1 text-info"></i> Change Password</a>
                   <div class="dropdown-divider"></div> 
				   <a class="dropdown-item text-danger" href="<?php echo  $BaseUrl.'/auth-login' ?>"><i class="ri-shut-down-line align-middle mr-1 text-danger"></i> Logout</a>
                </div>
            </div>

           <!-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="ri-settings-2-line"></i>
                </button>
            </div>-->
            
        </div>
    </div>
</header>

<?php


include "../includes/DbTables.php";


  $DeptID=$_SESSION['DeptID'];
  $SubDeptID=$_SESSION['SubDeptID'];
  $AccessType=$_SESSION["AccessType"]; 
  $SideBarHeaderTbl=$_SESSION["SideBarHeaderTbl"]; 
  $SideBarOptionTbl=$_SESSION["SideBarOptionTbl"];
    $UserRoleID=$_SESSION["UserRoleID"];
  
 if($AccessType==11){
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where Status='1'
	 and AccessType='$AccessType' and DeptID='$DeptID' order by SortOrder  asc";	
 }else{
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where Status='1' 
	 and AccessType>='$AccessType' and DeptID='$DeptID' order by SortOrder  asc";	 
 }
//echo $SqlSideBar;
if($UserRoleID!=0){
	$SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
	$StmtUserRoleDetails=$iconn->query($SqlUseRoleDetails);
	$RowUserDetails=$StmtUserRoleDetails->fetch_array();
	$Header=$RowUserDetails['Header'];
	
	
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where HeaderID in($Header) order by SortOrder  asc";
}
//ECHO $SqlSideBar; EXIT;
 $StmtSideBar=$iconn->query($SqlSideBar); 

?>			
			
			
		
									
									
									
									
			                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

                            <button id="btnMobileMenu"> <i class="ri-menu-2-line align-middle"></i></button>

                            <!-- <div class="collapse navbar-collapse" id="topnav-menu-content"> -->
                            <div class="navbar-collapse" id="topnav-menu-content">
                                <ul class="navbar-nav">

                                    
									
									<?php 
							while($SideBarRow=$StmtSideBar-> fetch_array()) {	
				
									$Sid= $SideBarRow['HeaderID'];?>
    
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle arrow-none" href="index.php" id="topnav-uielement" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="<?php echo $SideBarRow['SideMenuIcon'] ?>"></i><span><?php echo $SideBarRow['Header'] ?></span> <div class="arrow-down"></div>
                                        </a>

                                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                                            aria-labelledby="topnav-uielement">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div>
													
													<?php
		   
			$SqlSideBarOptions="SELECT * FROM `$SideBarOptionTbl` where DeptID='$DeptID' 
			 and HeaderId='$Sid' and AccessType >=' $AccessType' and Status=1 order by SortOrder  asc";	
			
		 
		   
		   if($UserRoleID!=0){
	$SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
	$StmtUserRoleDetails=$iconn->query($SqlUseRoleDetails);
	$RowUserDetails=$StmtUserRoleDetails->fetch_array();
	$Options=$RowUserDetails['Options'];
	
	
	 $SqlSideBarOptions="SELECT * FROM `$SideBarOptionTbl` where ID in($Options) and HeaderId='$Sid' order by SortOrder  asc";
}
else{
			 $SqlSideBarOptions="SELECT * FROM `$SideBarOptionTbl` where DeptID='$DeptID' 
and HeaderId='$Sid' and AccessType >=' $AccessType' and Status=1 order by SortOrder  asc";	
} 
  $StmtSideBarOptions=$iconn->query($SqlSideBarOptions); 
		   while($SideBarOptionsRow=$StmtSideBarOptions-> fetch_array()) { ?>
                                                        <a href="<?php echo $_SESSION["SideBarPath"].$SideBarOptionsRow['Directory'].$SideBarOptionsRow['PathUrl'] ?>" class="dropdown-item"><?php echo $SideBarOptionsRow['Name'] ?></a>
                                                      <?php } ?>
			
                                                    </div>
                                                </div>
                                                
                                            </div>

                                        </div>
                                    </li>
									
							<?php }?>
    
             
								   <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle arrow-none" href="index.php" id="topnav-uielement" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <img class="rounded-circle header-profile-user" src="<?php echo $AppLogo ?>" alt="Header Avatar">
                                                <span class="username"><?php echo  $_SESSION["UserName"] ;?> </span>
                                            <div class="arrow-down"></div>
                                        </a>

                                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                                            aria-labelledby="topnav-uielement">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div>
													
														<a href="<?php echo  $BaseUrl.'/ChangePassword' ?>" class="dropdown-item">Change Password</a>
														<a href="<?php echo  $BaseUrl.'/auth-login' ?>" class="dropdown-item">Logout</a>
                                                      			
                                                    </div>
                                                </div>
                                                
                                            </div>

                                        </div>
                                    </li>
								   
								   
								   
    
                                </ul>
								
                                <button type="button" class="btn header-item noti-icon waves-effect fullScreen" data-toggle="fullscreen">
                                    <i class="ri-fullscreen-line"></i>
                                </button>
                                <!-- <button class="btnMenuClose">close</button> -->
                            </div>
							
							
                        </nav>
                    </div>
                </div>
				<!--
						<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									</div>
								</div>
							</div>
						</div> -->

			
<?php }?>