<?php

include_once 'AppKeys.php';



function getTimeStamp()
{
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
	return $CurrentDateTime = $dateTime->format("Y-m-d  H:i:s");
}



// Encryption function
function encryptData($value)
{
	global $appsecretKey;
	$key=$appsecretKey;
	$cipher = "AES-256-CBC";
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
	$encrypted = openssl_encrypt($value, $cipher, $key, 0, $iv);
	return base64_encode($iv . $encrypted);
}

// Decryption function
function decryptData($encryptedValue)
{
	global $appsecretKey;
	$key=$appsecretKey;
	$cipher = "AES-256-CBC";
	$encryptedValue = base64_decode($encryptedValue);
	$ivLength = openssl_cipher_iv_length($cipher);
	$iv = substr($encryptedValue, 0, $ivLength);
	$encryptedData = substr($encryptedValue, $ivLength);
	return openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
}





function getEncryptedToken($payload){
	global $appsecretKey;
	$secretKey=$appsecretKey;

	$secret_key = "my_secret_key";

	// Create the header array.
	$header = [
		"alg" => "HS256",
		"typ" => "JWT"
	];
	
	// Create the payload array.
	$payload = [
		"iss" => "https://example.com",
		"sub" => "1234567890",
		"exp" => time() + (60 * 60 * 24), // Expires in 24 hours.
	];
	
	// Encode the header and payload arrays into a JSON string.
	$json_string = json_encode($header) . "." . json_encode($payload);
	
	// Sign the JSON string using the secret key.
	$signature = hash_hmac("sha256", $json_string, $secret_key);
	
	// Base64 encode the signature.
//	$signature = base64_encode($signature);
	
	// Combine the header, payload, and signature into a single string, separated by dots.
	return $token =  $signature;
	//return 'test';
}




/*


// Decrypt the token.
$header = json_decode(explode(".", $token)[0], true);
$payload = json_decode(explode(".", $token)[1], true);



// Verify the signature.
if (hash_hmac("sha256", $json_string, $secret_key) == $signature) {
    // The token is valid.
} else {
    // The token is invalid.
}
*/

?>