<?php

include_once '../Global/Util.php';
include_once '../Config/Token.php';
class DigitalSignatureController
{

    private $conn;
    private $db_table = "ScrmClientInputFields";

    public $id;
    public $name;
    
    // Db connection
    public function __construct($db)
    {
        //$this->conn = $db;
        $database = new Database(); 
        $this->restApi = new REST(); 
        $this->conn =$database->getAuthConnection();
    }
   
    public function getDigitalSignature($RequestBody)
    {

        //$authToken = $this->restApi->getRequestHeader();
      // $payload = Token::Verify($authToken);
      
       $ApprovedByID = $RequestBody['ApprovedByID'];
       $Type = $RequestBody['Type'];
       $LoginID = $RequestBody['LoginID'];

       if($Type=='Preparedby'){
$Query="WHERE UserID='$LoginID'";
       }

       else if ($Type=='Sign'){
        $Query="WHERE UserID='$ApprovedByID'";
       }

        //$sqlQuery="SELECT FieldID FROM `ScrmClientInputFields` WHERE ClientID='$ClientID' and DeptID in ($DeptID)and Status='1'";
        //  $sqlQuery="SELECT SignURL FROM `AdminLoginDetails` WHERE UserID='$ApprovedByID'";
         $sqlQuery="SELECT SignURL,UserName FROM `AdminLoginDetails`  $Query";  
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 
        //$SignURL=$dataRow['SignURL'];

     
   
     //   while( $dataRowInputFields = $stmtDataQuery->fetch(PDO::FETCH_ASSOC)){
            $ArraySignature['SignURL'] =$dataRow['SignURL'];
            $ArraySignature['Preparedby'] =$dataRow['UserName'];
          

           // $Arysignature[] = $ArraySignature;
    //  } 
      if($stmt){
      $data['Error'] = null;
      $data['Msg'] = 'Success';
      $data['Data'] = $ArraySignature;
       return $data;
    }

    else{
        $data['Error'] = null; 
        $data['Msg'] = 'Data not found';
    }
}
}
?>