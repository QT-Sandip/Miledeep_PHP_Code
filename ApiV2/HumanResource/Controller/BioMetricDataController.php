<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
ini_set('memory_limit', '1024M');
set_time_limit(0);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class BioMetricDataController
{
    private $conn;    
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getEmployeeCreationDetails($RequestBody)
    {
        $EmployeeID = $RequestBody['EmployeeID'];
        $EmployeeData = $this->getEmployeeCreationData($EmployeeID);

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';

        $Data['Data'] = $EmployeeData;

        return $Data;
    }

    public function postAttendanceData($RequestBody)
    {

        $Data = $this->postBioMetricData($RequestBody);
        return $Data;

    }

    public function postBioMetricData($RequestBody)
    {

        //set_time_limit(300); // Allow up to 5 minutes
        //ini_set('memory_limit', '512M'); // Increase memory if large data set
        $CheckIn = 0;
        $CheckOut = 1;

        // $JsonIpData = file_get_contents('php://input');
        // $AryData = json_decode($JsonIpData,true);        
        //$AryData = $RequestBody;
        $RequestBody = file_get_contents("php://input");
        $AryData = json_decode($RequestBody);
        if (is_string($AryData)) {
            $AryData = json_decode($AryData,true);
        }
        if (is_string($AryData)) {
            $AryData = json_decode($AryData,true);
        }
        $Error = $AryData['Error'];
        $ClientID = $AryData['ClientID'];
        $UserID = $AryData['UserID'];
        $MachineIp = $AryData['MachineIp'];
        $UserIp = $AryData['UserIp'];
        $AttendanceData = $AryData['AttendanceData'];

        $SqlGetBioMetricDevices =" select * from HrmBioMetricDevices ";
        $BioMetricDeviceList = [];
        $stmtBioMetricDevices = $this->conn->prepare($SqlGetBioMetricDevices);
        $stmtBioMetricDevices->execute();
        while ($rowBioMetricDevices = $stmtBioMetricDevices->fetch(PDO::FETCH_ASSOC)) {
            $BioMetricDeviceList[$rowBioMetricDevices['IPAddress']] = $rowBioMetricDevices['IPAddress'];
        }

        if(!$BioMetricDeviceList[$MachineIp]){
            echo "BioMetric Device IP not matched";
            exit;
        }

        $TempTable = 'HrmDailyAttendnessTemp';
        
        $SqlPreviousDate = " delete from ".$TempTable." where DeviceID = '".$MachineIp."'";
        $stmt = $this->conn->prepare($SqlPreviousDate);
        $stmt->execute();

        $batchSize = 200;
        $SqlTempArray = [];
        $totalInserted = 0;
        $totalFailed = 0;     

        foreach ($AttendanceData as $RowAttData) {

            $BioMetricId = $RowAttData['BioMetricId'];
            $Date = date("Y-m-d", strtotime($RowAttData['Date']));
            $TimeIn = date("H:i:s", strtotime($RowAttData['Date']));
            $Status = $RowAttData['PunchType'];
            $DateBioID = str_replace('-', '', $Date);
            $DateBioID = $DateBioID . $BioMetricId;
            $SqlTempArray[] = "('$BioMetricId','$DateBioID','$Date','$TimeIn','$Status','$MachineIp')";

        }

        $total = count($SqlTempArray);
        for ($i = 0; $i < $total; $i += $batchSize) {    
            
            $batch = array_slice($SqlTempArray, $i, $batchSize);
            $SqlTempValues = implode(',', $batch); 
            if ($SqlTempValues) {

                $SqlTemp = "INSERT IGNORE INTO $TempTable (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
                try {

                    $sqlca_post = $this->conn->prepare($SqlTemp);
                    if($sqlca_post->execute()){

                        $SqlTemp = "INSERT IGNORE INTO HrmDailyAttendnessBackUp (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
                        $sqlca_post = $this->conn->prepare($SqlTemp);
                        $sqlca_post->execute();
                        $sqlca_post = true;

                    }else {
                        $totalFailed += count($SqlTempArray);
                    }

                } catch (PDOException $e) {
                    $totalFailed += count($SqlTempArray);
                    error_log("Insert failed: " . $e->getMessage()); // Log failure
                }

            }    

        }  
    
    if (isset($sqlca_post) && $sqlca_post) {    
        
        $records = array('Error' => null, "Msg" => "Data Saved successfully");
        //$this->response($this->json($records), 200);
     } else {
         $records = array('Error' => "Unable To Submit Data", "Msg" => "Data saved successfully");
         //$this->response($this->json($records), 200);
     }

     return $records;
    }

}
?>
