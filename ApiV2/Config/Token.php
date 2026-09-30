<?php

class Token
{

    /**
     * Sign - Static method to generate token
     *  
     * @param array $payload
     * @param string $key - The signature key
     * @param int $expire - (optional) Max age of token in seconds. Leave it blank for no expiration.
     * 
     * @return string token
     */
    static function getEncryptedToken($payload)
    {
        $Secretkey='Mykey';
        $expire=100000;
        // Header
        $headers = ['algo' => 'HS256', 'type' => 'JWT', 'expire' => time() + $expire];
        if ($expire) {
            $headers['expire'] = time() + $expire;
        }
        $headers_encoded = base64_encode(json_encode($headers));

        // Payload
        $payload['time'] = time();
        $payload_encoded = base64_encode(json_encode($payload));

        // Signature
        $signature = hash_hmac('SHA256', $headers_encoded . $payload_encoded, $Secretkey);
        $signature_encoded = base64_encode($signature);

        // Token
        $token = $headers_encoded . '.' . $payload_encoded . '.' . $signature_encoded;

        return $token;
    }

    /**
     * Verify - Static method verify token
     * 
     * @param string $token
     * @param string $key - The signature key
     * 
     * @return boolean false if token is invalid or expired
     * @return array payload
     */
    static function Verify($token)
    {
        $Secretkey='Mykey';
       
        //$token='eyJhbGdvIjoiSFMyNTYiLCJ0eXBlIjoiSldUIiwiZXhwaXJlIjoxNjkxNjc2MDgyfQ==.eyJVc2VySUQiOiI2IiwiQ2xpZW50SUQiOiJTTFMyMDIxMDAzIiwidGltZSI6MTY5MTY3NjA4Mn0=.MzAxMTJlYjUxMjJjZWEwNjIzYzcyNDU3YjM0YWI4ZGQ3NTgyOTcxYmQ0NTliNWI1MGNjYWI3ODY0ZjdkZjNhYQ==';
        // Break token parts
        $token_parts = explode('.', $token);

        // Verigy Signature
        $signature = base64_encode(hash_hmac('SHA256', $token_parts[0] . $token_parts[1], $Secretkey));
        if ($signature != $token_parts[2]) {
            return false;
        }

        // Decode headers & payload
         $headers = json_decode(base64_decode($token_parts[0]), true);
        $payload = json_decode(base64_decode($token_parts[1]), true);

        // Verify validity
        if (isset($headers['expire']) && $headers['expire'] < time()) {
            return false;
        }

        // If token successfully verified
        return $payload;
    }

}



/*


$KEY = 'thisisakey';

$token = Token::Sign(['id' => 'demo-id'], $KEY, 60 * 5);


echo $token;


// $t = "eyJhbGdvIjoiSFMyNTYiLCJ0eXBlIjoiSldUIiwiZXhwaXJlIjoxNjkxNjc0MTE5fQ==.eyJpZCI6ImRlbW8taWQiLCJ0aW1lIjoxNjkxNjczODE5fQ==.OGVjZTBiYWZkZGIxZmU3OGMwZWY5OTJlN2M3ZWVlYjI1NGM0MTFiM2Y5NTM0MDE2ZTA1MmU4YzgyOTQwMGU4MA==";


$payload = Token::Verify($t, $KEY);

echo "<br>";
var_dump($payload);

*/

?>