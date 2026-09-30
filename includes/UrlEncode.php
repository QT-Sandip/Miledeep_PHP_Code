<?php
function urlEncode64($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function urlDecode64($input) {
 return base64_decode(strtr($input, '._-', '+/='));
} 

?>
 