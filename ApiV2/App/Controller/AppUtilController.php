<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once '../Global/Util.php';
include_once '../Config/Config.php';
include_once '../Config/Rest.php';
include_once '../Config/Token.php';

class AppUtilController
{

  //  
    private $database;
    private $restApi;
    private $conn;
 
    // Db connection
    public function __construct($db)
    {
        $database = new Database(); 
        $this->restApi = new REST(); 
        $this->conn =$database->getAuthConnection();
    }

    public function getUserRoleID($UserID)
    {
        $sqlQuery = "SELECT * FROM `AdminLoginDetails` WHERE UserID=$UserID ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }

    public function getClientRoleID($UserID)
    {
        $sqlQuery = "SELECT ALD.ClientID,RoleID,ARC.BrandLogo FROM `AdminLoginDetails` as ALD left Join AdminRegisteredClients AS ARC on ALD.ClientID=ARC.ClientAccountID WHERE UserID=$UserID ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }


    // public function getClientConfig($UserID)
    // {
    //     $sqlQuery = "SELECT ARC.* FROM `AdminLoginDetails` as ALD left Join AdminConfigDetails AS ARC on ALD.ClientID=ARC.ClientID WHERE UserID=$UserID ";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     if($stmt->rowCount()==0){
    //         $sqlQuery = "SELECT ARC.* FROM `AdminLoginDetails` as ALD left Join AdminConfigDetails AS ARC on ALD.ClientID=ARC.ClientID WHERE ClientID=0";
    //         $stmt = $this->conn->prepare($sqlQuery);
    //         $stmt->execute();
    //     }
    //     $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $dataRow;
    // }
    
    public function getClientConfig($ClientID)
    {
        $sqlQuery = "SELECT * FROM  AdminConfigDetails  WHERE ClientID='$ClientID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if($stmt->rowCount()==0){
            $sqlQuery = "SELECT * FROM  AdminConfigDetails  WHERE ClientID='0'";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
        }
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
 
    public function getClientInvoiceConfig($ClientID)
    {
        $sqlQuery = "SELECT * FROM  AdminInvoiceConfigDetails  WHERE ClientID='$ClientID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }

    public function getItemTableLables($requestBody)
    {

        $authToken = $this->restApi->getRequestHeader();
        $payload = Token::Verify($authToken);
        $ClientID=$payload['ClientID'];
        $DocType=$requestBody['DocType'];
        $Buyer=$requestBody['Buyer'];
        $Country=$requestBody['Country'];

        if($ClientID==''){
            $Qry .= "and ClientID in (0)";
        }else{
            $Qry .= "and ClientID in ('$ClientID')"; 
        }

        if($DocType==''){
            $Qry .= "and DocType in (0)"; 
        }else{
            $Qry .= "and DocType in ('$DocType')"; 
        } 

        if($Buyer==''){
            $Qry .= "and Buyer in (0)";
        }else{
            $Qry .= "and Buyer in (0,'$Buyer')"; 
        }

        if($Country==''){
            $Qry .= "and Country in (0)";
        }else{
            $Qry .= "and Country in ('$Country')"; 
        }


            $sqlQuery = "SELECT * FROM `ScrmInvItemListLabels` WHERE Status=1 $Qry order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
     if($stmt->rowCount()==0){
        $sqlQuery = "SELECT * FROM `ScrmInvItemListLabels` WHERE Status=1 and DefaultKeys=1 order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();   
    }
 $sqlQuery;
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            if(trim(($dataRow['ExtKey']))!=''){
                $ext= $this->getExt($requestBody,$dataRow['ExtKey']);
            }else{
                $ext='';
                        }
            
        
        $arykeys[] = $dataRow['Key'];
        $aryLables[$dataRow['Key']] = $dataRow['Value'] .' '.$ext;
       
        
    }
    $ItemData['TableKeys']=$arykeys;
    $ItemData['TableLables']=$aryLables;
        return $ItemData;
    }  


    function getExt($requestBody,$Key){
       
       return isset($requestBody[$Key])?$requestBody[$Key] :'';


  
    }
    


}

?> 