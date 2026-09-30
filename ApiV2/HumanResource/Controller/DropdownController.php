<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 

class DropdownController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    private $Driver = "1";
    private $Grader = "2";
    private $Agent = "3";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDropdowns()
    {
      
        $Data['Department']=$this->getDepartments();
        $Data['SubDepartments']=$this->getSubDepartment();
        $Data['Designations']=$this->getDeginations();
        $Data['SalaryStructure']=$this->getSalaryStructures();
        $Data['Branches']=$this->getBranchesList();
        $Data['Contractors']=$this->getContractorsList();
        $Data['Category']=$this->getCategory();
        $Data['SubCategory']=$this->getSubCategory();
        $Data['Gender']=$this->getGender();
        $Data['Shift']=$this->getShift();
        $Data['MaritalStatus']=$this->getMaritalStatus();
        $Data['ESIApplicable']=$this->getESIApplicable();
        $Data['PFApplicable']=$this->getPFApplicable();
        $Data['TDSApplicable']=$this->getTDSApplicable();
        $Data['BloodGroup']=$this->getBloodGroup();
        $Data['BankDetails']=$this->getBankDetails();
        
      
     

      
        return $Data;
    }


    function getDepartments(){


        $sqlQuery = "SELECT DeptID,DepartmentName FROM `HrmDepartments` ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }

    function getSubDepartment(){


        $sqlQuery = "SELECT SubDeptID,DeptID,SubDeptName FROM `HrmSubDepartments`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }


    function getDeginations(){


        $sqlQuery = "SELECT DesigID,Designation FROM `HrmDesignations`  ORDER by Designation Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getBranchesList(){


        $sqlQuery = "SELECT BranchID,BranchName FROM `HrmBranchMaster` ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getContractorsList(){


         $sqlQuery = "SELECT ContractorID,ContractorName FROM HrmContractorList Order by ContractorID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    // function getVehiclesDetails(){


    //     $sqlQuery = "SELECT ID as VehicalID,VehicalNumber FROM `RmVehicalDetails` order by VehicalNumber asc";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
        
    //     while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $Data[]=$dataRow;
    //     }
    //     return $Data;
    // }
    // function getUsers($UserType){


    //     $sqlQuery = "SELECT ID as UserID ,UserName FROM `RmUsersList`  WHERE UserType='$UserType' order By UserName asc";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $Data[]=$dataRow;
    //     }

    //     return $Data; 
    // }


    
    function getSalaryStructures(){


        $sqlQuery = "SELECT ID,RoleName FROM `HrmSalaryStructureMaster` WHERE Status=1;";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }

    // Durga
    function getCategory() {

        $Data = array("Worker","Staff");
        
        return $Data;
    }
    function getSubCategory() {
        $Data = array("Regular","ContractWise");
        
        return $Data;
    }
    function getShift() {
       $sqlQuery = "SELECT ShiftID,ShiftName FROM HrmShiftDetails";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[] =$dataRow;
        }
        return $Data;
    }
    function getMaritalStatus() {
        $Data = array("Married","UnMarried");
        
        return $Data;
    }
    function getESIApplicable() {
        $Data = array("Yes","No");
        
        return $Data;
    }
    function getPFApplicable() {
        $Data = array("Yes","No");
        
        return $Data;
    }
    function getTDSApplicable() {
        $Data = array("Yes","No");
        
        return $Data;
    }
    function getGender() {
        $Data = array("Male","Female","Others");
        
        return $Data;
    }
    function getBloodGroup() {
        $Data = array("O Positive","O Negative","A Positive","A Negative","B Positive","B Negative","AB Positive","AB Negative");
        
        return $Data;
    }
    function getBankDetails() {
       $sqlQuery = "SELECT ID as BankNameID,BankName FROM HrmBanksMaster";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[] =$dataRow;
        }
        // var_dump($Data);
        return $Data;
    }
}
?>
