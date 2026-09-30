<?php if($_SESSION["ClientID"]=='SLS20210044'){ ?>

<?php
session_start();

include "../includes/DbTables.php";


  $DeptID=$_SESSION['DeptID'];
  $SubDeptID=$_SESSION['SubDeptID'];
  $AccessType=$_SESSION["AccessType"]; 
  $SideBarHeaderTbl=$_SESSION["SideBarHeaderTbl"]; 
  $SideBarOptionTbl=$_SESSION["SideBarOptionTbl"];
   $UserRoleID=$_SESSION["UserRoleID"];
 
  
 if($AccessType==1111){
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where Status='1'
	 and AccessType='$AccessType' and DeptID='$DeptID' order by SortOrder  asc";	
 }else{
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where Status='1' 
	 and AccessType>='$AccessType' and DeptID='$DeptID' order by SortOrder  asc";	 
 }

if($UserRoleID!=0){
	$SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
	$StmtUserRoleDetails=$iconn->query($SqlUseRoleDetails);
	$RowUserDetails=$StmtUserRoleDetails->fetch_array();
	$Header=$RowUserDetails['Header'];
	
	
	 $SqlSideBar="SELECT * FROM `$SideBarHeaderTbl` where HeaderID in($Header) and Status='1' order by SortOrder  asc";
}

//echo $SqlSideBar; exit;
 $StmtSideBar=$iconn->query($SqlSideBar); 

?>



<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Menu</li>

				
				<?php 
				
				while($SideBarRow=$StmtSideBar-> fetch_array()) {	
				
							$Sid= $SideBarRow['HeaderID'];?>
					<li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="<?php echo $SideBarRow['SideMenuIcon'] ?>"></i>
                        <span><?php echo $SideBarRow['Header'] ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
					<?php
		   
		 
			 
			 if($UserRoleID!=0){
	$SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
	$StmtUserRoleDetails=$iconn->query($SqlUseRoleDetails);
	$RowUserDetails=$StmtUserRoleDetails->fetch_array();
	$Options=$RowUserDetails['Options'];
	
	
	 $SqlSideBarOptions="SELECT * FROM `$SideBarOptionTbl` where ID in($Options) and HeaderId='$Sid' and Status='1' order by SortOrder  asc";
}
else{
			$SqlSideBarOptions="SELECT * FROM `$SideBarOptionTbl` where DeptID='$DeptID' 
and HeaderId='$Sid' and AccessType >=' $AccessType' and Status=1 order by SortOrder  asc";	
} 
//echo $SqlSideBarOptions;
		   $StmtSideBarOptions=$iconn->query($SqlSideBarOptions); 

		   while($SideBarOptionsRow=$StmtSideBarOptions-> fetch_array()) { ?>
		    <li><a href="<?php echo $_SESSION["SideBarPath"].$SideBarOptionsRow['Directory'].$SideBarOptionsRow['PathUrl'] ?>"><?php echo $SideBarOptionsRow['Name'] ?></a></li>
			
			<?php } ?>
			
			</ul>
                </li>
				
				<?php } ?>

                
               

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->



<?php } ?>
