<?php
include_once 'Token.php';
include_once 'Rest.php';

$restApi = new REST();
class Database
{

     

   // private $host = '153.92.6.103';
    private $host = 'localhost';
    private $database_name = 'u177334400_CamarooAdmin';
    private $username = 'u177334400_CamarooAdmin';
    private $password = '0$Kzi|mT1^~>';
    public $conn;
    public function getAuthConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Database could not be connected: " . $exception->getMessage();
        }
        return $this->conn;
    }


    public function getConnection($authToken)
    {
        global $restApi;
        try {

            $DbDetails = $this->getDbConfig($authToken);
            $this->conn = null;


            if ($DbDetails != null) {



                $host = $DbDetails['AuthDbHost'];
                $host = '153.92.6.103';
                $database_name = $DbDetails['AuthDbName'];
                $username = $DbDetails['AuthDbUserName'];
                $password = $DbDetails['AuthDbPassword'];
                
             /*   $host = 'localhost';
                $database_name = 'camaroo_dfl';
                $username = 'camaroo_dfl_test_user';
                $password = 'dfl_test_user';
 
*/
                $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database_name, $username, $password);
                $this->conn->exec("set names utf8");
                return $this->conn;
            } else {
              
               $responseData['Error']='Unauthrized Token';
               $responseData=json_encode($responseData);
               $restApi->response($responseData,401);
            }
        } catch (PDOException $exception) {
 
            $responseData['Error']='Database Connection Error';
            $responseData=json_encode($responseData);
            $restApi->response($responseData,200);

        }

    }


    private function getDbConfig($authToken)
    {
        //echo $authToken;
        $payload = Token::Verify($authToken);
        //   var_dump($payload);
        if ($payload) {

            $AuthUserID = $payload['UserID'];

            $db = $this->getAuthConnection();

            $sqlFields = 'AuthDbHost,AuthDbName,AuthDbUserName,AuthDbPassword,LastAccess';
            $sqlQuery = "SELECT $sqlFields FROM `AuthLoginDetails` as ALD 
                    Left join AuthClientDbDetails as ACDB  on ACDB.ClientID=ALD.ClientID WHERE AuthUserID= :AuthUserID";
            $stmt = $db->prepare($sqlQuery);
            $stmt->bindParam(':AuthUserID', $AuthUserID);

            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            return $dataRow;
        } else {
            return null;
        }



    }
}
?>