<?php

include_once '../Global/Util.php';
include_once 'AppUtilController.php';

class MenuController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    private $appUtilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->appUtilController = new AppUtilController($this->conn);
    }
    // GET ALL
    public function getMenu($payLoad)
    {
       // var_dump($payLoad);
        $UserID=$payLoad['UserID'];
        $DeptID=$payLoad['DeptID'];

        $aryUserData = $this->appUtilController->getUserRoleID($UserID);

        $UserRoleID=$aryUserData['UserRoleID'];
        $sqlQuery = "SELECT SideBarHeaderTbl,SideBarOptionTbl FROM `AdminDepartments` WHERE DeptID='$DeptID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if($stmt){
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $MenuTable=$dataRow['SideBarHeaderTbl'];
        $SubMenuTable=$dataRow['SideBarOptionTbl'];

        if($UserRoleID!=0){
            $SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
            $stmt = $this->conn->prepare($SqlUseRoleDetails);
            $stmt->execute();
            $RowUserDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
            $Header=$RowUserDetails['Header'];

        $sqlQuery = "SELECT HeaderID,Header,SideMenuIcon FROM $MenuTable WHERE Status=1 and HeaderID in ($Header) order by SortOrder asc";
        }else{
            $sqlQuery = "SELECT HeaderID,Header,SideMenuIcon,Sid FROM $MenuTable WHERE Status=1  order by SortOrder asc";   
        }
        error_log($sqlQuery);
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $headerIDsAry = [];
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $headerIDsAry[] = $dataRow['Sid'];
            $HeaderID=$dataRow['HeaderID']; 
            $MenuData['MenuName']=$dataRow['Header']; 
            $MenuData['MenuIcon']=$dataRow['SideMenuIcon']; 
            $MenuData['SubMenu']=$this->getSubMenu($HeaderID,$SubMenuTable,$UserRoleID);
            $MenuData['PageTitles']=$this->getPageTitle($HeaderID,$SubMenuTable);
            $Data[]=$MenuData; 
        }

        }
        $HeaderIDs = implode(',', $headerIDsAry);      
        $Data['HeaderIDs'] = $HeaderIDs;
        $Data['OptionIDs'] = $this->getSubMenuIDs($HeaderIDs,$SubMenuTable,$UserRoleID);
      return $Data;
    }


    function getPageTitle($HeaderID,$SubMenuTable){

$Link='Purchase/create-booking';
        $sqlQuery = "SELECT Name,PathUrl FROM $SubMenuTable WHERE Status=1 and HeaderId=$HeaderID  order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $dataRow['Location']='Purchase'; 
            $dataRow['Title']='New Booking'; 
            $dataRow['link']=$Link; 
            $Link='';
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getSubMenu($HeaderID,$SubMenuTable,$UserRoleID){


        if($UserRoleID!=0){
            $SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
            $stmt = $this->conn->prepare($SqlUseRoleDetails);
            $stmt->execute();
            $RowUserDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            $Options=$RowUserDetails['Options'];  
                $sqlQuery="SELECT * FROM `$SubMenuTable` where ID in($Options) and HeaderId=$HeaderID order by SortOrder  asc";
            }else{
                $sqlQuery = "SELECT Name,Directory,PathUrl FROM $SubMenuTable WHERE Status=1 and HeaderId=$HeaderID  order by SortOrder asc";
            }
       
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $dataRow['PathUrl']=$dataRow['Directory'].''.$dataRow['PathUrl']; 
            $dataRow['Location']='Exports'; 
            $dataRow['PrintPage']='Print'; 
            $dataRow['EditPage']='Edit '; 
            $dataRow['AddPage']='Add '; 
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getSubMenuIDs($headerIDs, $SubMenuTable, $UserRoleID){

        $sqlQuery = "SELECT GROUP_CONCAT(ID ORDER BY SortOrder ASC) as IDs 
                     FROM $SubMenuTable 
                     WHERE Status = 1 
                     AND HeaderId IN ($headerIDs)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['IDs'];
    }
}
?>