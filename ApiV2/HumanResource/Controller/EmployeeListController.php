<?php


include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class EmployeeListController
{
    private $conn;
    private $db_table = "ScrmCustomInvoices";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetEmployeeListDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        
        $EmpStatus = $RequestBody['EmployeeStatus'];
        $EmpDept = $RequestBody['DeptID'];
        $EmpSubDept = $RequestBody['SubDeptID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        
       
        $Query = '';
        $SqlEmpCount = '';
        $SqlFieldsList = '';
        $SqlEmpDetailsQuery = '';


        if ($EmpStatus != "") {
            $Query .= "and HED.EmpStatus =$EmpStatus";
        }

        if ($EmpDept != "") {
            $Query .= " and HED.DeptID=$EmpDept";
        }

        if ($EmpSubDept != "") {
            $Query .= " and HED.SubDeptID=$EmpSubDept";
        }


        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }
        
        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', EmpName,BioMetricID ) LIKE '%$SearchKey%'";
        }

        $SqlEmpCount = "SELECT count(*) as Total  FROM HrmEmployeeDetails as HED  WHERE EmpID!=0 $Query ";

        $stmt = $this->conn->prepare($SqlEmpCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $SqlFieldsList = "EmpID,
                            BioMetricID,
                            EmpName,
                            DepartmentName,
                            SubDeptName,
                            Designation,
                            ContractorID";

        $SqlEmpDetailsQuery = "SELECT $SqlFieldsList FROM HrmEmployeeDetails as HED 
        left join HrmDepartments as HrmD on HrmD.DeptID=HED.DeptID
        left join HrmSubDepartments as HrmSD on HrmSD.DeptID=HrmD.DeptID
        left join HrmDesignations  as HrmDg on HrmDg.DesigID=HED.DesigID
        WHERE  HED.EmpID!=0  $Query  order by HED.BioMetricID  $Limit";
   
       
        $stmt = $this->conn->prepare($SqlEmpDetailsQuery);
        $stmt->execute();

        $AryCustomData = [];
        // $i = 0;
        $i =$LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

          

          //  $ArrayCustomDetails['SNO'] = ++$i;
           
            $AryCustomData[] = $dataRow;

        }
        if (count($AryCustomData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-employee?EmpID=";
        $data['DownloadExcel'] = "#";
        $data['Data'] = $AryCustomData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        return $data;
    }

}
?>