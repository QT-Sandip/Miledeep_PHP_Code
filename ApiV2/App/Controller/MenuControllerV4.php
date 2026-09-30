<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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
        $aryAdminData = $this->appUtilController->getClientRoleID($UserID);

        $UserRoleID=$aryUserData['UserRoleID'];
        $ClientsRoleID=$aryAdminData['RoleID']; // Given Features for client
        $ClientLogo=$aryAdminData['BrandLogo'];
       
        $SqlClientRoles="SELECT * FROM `AdminClientRoles` WHERE ID='$ClientsRoleID'";
        $stmt = $this->conn->prepare($SqlClientRoles);
        $stmt->execute();
        $RowClientDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $AryClientMenu= explode(',',$RowClientDetails['Header']);
        $AryClientSubMenu= explode(',',$RowClientDetails['Options']);



        if($UserRoleID!=0){
            $SqlUseRoleDetails="SELECT * FROM `AdminUserRoles` WHERE ID='$UserRoleID'";
            $stmt = $this->conn->prepare($SqlUseRoleDetails);
            $stmt->execute();
            $RowUserDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
           
            $AryHeader=explode(',',$RowUserDetails['Header']);
            $AryOptions=explode(',',$RowUserDetails['Options']);

            foreach($AryHeader as $UserMenuItem){
                if(in_array($UserMenuItem ,$AryClientMenu,true)){
                    $AryUserMenu[]=$UserMenuItem;
                  
                }
            }
            $Header=implode(',', $AryUserMenu);

            foreach($AryOptions as $UserSubMenuItem){
                if(in_array($UserSubMenuItem ,$AryClientSubMenu,true)){
                    $AryUserSubMenu[]=$UserSubMenuItem;
                    
                }
            }
            $Options=implode(',',$AryUserSubMenu);


            $sqlQuery = "SELECT Sid,HeaderID,Header,SideMenuIcon FROM ClientSideBarHeaders WHERE Status=1 and Sid in ($Header) order by SortOrder asc";
        }else{
            $sqlQuery = "SELECT Sid,HeaderID,Header,SideMenuIcon FROM ClientSideBarHeaders WHERE Status=1  and Sid in (0)order by SortOrder asc";   
        }

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $HeaderID=$dataRow['Sid']; 
            $MenuData['MenuName']=$dataRow['Header']; 
            $MenuData['MenuIcon']=$dataRow['SideMenuIcon']; 
            $MenuData['SubMenu']=$this->getSubMenu($HeaderID,$AryClientSubMenu,$AryOptions);
            $MenuData['PageTitles']=$this->getPageTitle($HeaderID);
            $Data[]=$MenuData; 
        }
        $Data['HeaderIDs'] = $Header;
        $Data['OptionIDs'] = $Options;
        $Data['RootUrl'] = $this->getRootUrl($Header,$Options);
        $Data['BrandLogo'] = $ClientLogo;
  //      }
      return $Data;
    }


    function getPageTitle($HeaderID){

$Link='Purchase/create-booking';
        $sqlQuery = "SELECT Name,FileName as PathUrl FROM ClientSideBarOptions WHERE Status=1 and HeaderId=$HeaderID  order by SortOrder asc";
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
    function getSubMenu($HeaderID,$AryClientSubMenu,$AryOptions){



        foreach($AryOptions as $UserSubMenuItem){
            if(in_array($UserSubMenuItem ,$AryClientSubMenu,true)){
                $AryUserSubMenu[]=$UserSubMenuItem;
                
            }
        }
      // var_dump($AryUserSubMenu);
        

        $Options=implode(',',$AryUserSubMenu);
         $sqlQuery="SELECT *, FileName as PathUrl FROM `ClientSideBarOptions` where ID in($Options) and HeaderId=$HeaderID order by SortOrder  asc";
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

    function getRootUrl($Headers,$Options){
        
      $SqlMenu = "SELECT
                    Headers.Sid,
                    Headers.Header,
                    OPTIONS.ID,
                    OPTIONS.Directory,
                    OPTIONS.Name,
                    OPTIONS.FileName,
                    OPTIONS.Module,
                    Modules.MenuIcon,
                    Headers.SortOrder,
                    OPTIONS.SortOrder,
                    Modules.SortOrder
                FROM
                    `ClientSideBarOptions` OPTIONS
                LEFT JOIN ClientSideBarHeaders Headers ON OPTIONS
                    .HeaderId = Headers.Sid
                LEFT JOIN ClientSideBarModules Modules ON OPTIONS
                    .Module = Modules.Name
                WHERE
                    Headers.Sid IN($Headers) AND ID IN( $Options ) AND Modules.SortOrder!=''  AND Headers.Header!='Master'
                ORDER BY
                    Modules.SortOrder ASC,
                    Headers.SortOrder ASC,
                    OPTIONS.SortOrder ASC LIMIT 1";   
        

        $stmt = $this->conn->prepare($SqlMenu);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data['Directory'].''.$data['FileName'];
    }

}
?>