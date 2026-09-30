<?php


include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class StudentListController
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
    public function GetCustomFeildDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        
        $FeildBelongTo = $RequestBody['FeildBelongTo'];
        $FeildType = $RequestBody['FeildType'];
        $FeildName = $RequestBody['FeildName'];
        $Grid = $RequestBody['Grid'];
        $FeildValues = $RequestBody['FeildValues'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
       
        $Query = '';
        $SqlEmpCount = '';
        $SqlFieldsList = '';
        $SqlEmpDetailsQuery = '';


        if ($FeildBelongTo != "") {
            $Query .= "and HED.FeildBelongTo ='. $FeildBelongTo.'";
        }

        if ($FeildType != "") {
            $Query .= " and HED.FeildType=' . $FeildType.'";
        }

        if ($FeildName != "") {
            $Query .= " and HED.FeildName=' . $FeildName.'";
        }
        if ($Grid != "") {
            $Query .= " and HED.Grid=' . $Grid.'";
        }
        if ($FeildValues != "") {
            $Query .= " and HED.FeildValues=' . $FeildValues.'";
        }


        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', FeildName,FeildType ) LIKE '%$SearchKey%'";
        }

        $SqlStdCount = "SELECT count(*) as Total  FROM custom_fields as CSF  WHERE id!=0 $Query ";

        $stmt = $this->conn->prepare($SqlStdCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $SqlFieldsList = "BioMetricID,
                            EmpName,
                            DepartmentName,
                            SubDeptName,
                            Designation,
                            ContractorID";

        $SqlStdDetailsQuery = "SELECT $SqlFieldsList FROM custom_fields as CSF 
        left join custom_field_values as CFV on CFV.id=CSF.id
        WHERE  CSF.id!=0  $Query  order by CSF.id $Limit";

        $stmt = $this->conn->prepare($SqlStdDetailsQuery);
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
        $data['EditLink'] = "AddCustomInvoice.php?PIID=";
        $data['DownloadExcel'] = "Reports/DocProformaExcel.php?id=";
        $data['Data'] = $AryCustomData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        return $data;
    }

}
?>