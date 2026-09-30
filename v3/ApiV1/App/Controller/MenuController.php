<?php

include_once '../Global/Util.php';

class MenuController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getMenu($payLoad)
    {

        $DeptID=$payLoad['DeptID'];
        $sqlQuery = "SELECT SideBarHeaderTbl,SideBarOptionTbl FROM `AdminDepartments` WHERE DeptID='$DeptID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if($stmt){
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $MenuTable=$dataRow['SideBarHeaderTbl'];
        $SubMenuTable=$dataRow['SideBarOptionTbl'];

        $sqlQuery = "SELECT HeaderID,Header,SideMenuIcon FROM $MenuTable WHERE Status=1  order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $HeaderID=$dataRow['HeaderID']; 
            $MenuData['MenuName']=$dataRow['Header']; 
            $MenuData['MenuIcon']=$dataRow['SideMenuIcon']; 
            $MenuData['SubMenu']=$this->getSubMenu($HeaderID,$SubMenuTable);
            $Data[]=$MenuData; 
        }

        }
      
      return $Data;
    }

    function getSubMenu($HeaderID,$SubMenuTable){


        $sqlQuery = "SELECT Name,PathUrl FROM $SubMenuTable WHERE Status=1 and HeaderId=$HeaderID  order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $dataRow['Location']='Exports'; 
            $Data[]=$dataRow;
        }
        return $Data;
    }

}
?>