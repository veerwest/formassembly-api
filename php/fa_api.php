<?php

// Values we'll need through the process
$CLIENT_ID=htmlspecialchars("xxxx"); // Issued by FormAssembly host
$CLIENT_SECRET=htmlspecialchars("xxxx"); // Issued by FormAssembly host
// Auto generate our return url for wherever this page is located.
$RETURN_URL= (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
// Replace xxxxxx with correct url
$AUTH_ENDPOINT="https://xxxxxx/oauth/login"; 
// Replace xxxxxx with correct url
$TOKEN_REQUEST_ENDPOINT="https://xxxxxx/oauth/access_token";

// Set our API Endpoint
// https://server/api_v1/forms/index.xml
// Replace xxxxxx with correct url
$API_REQUEST="https://xxxxxx/api_v1/forms/index.json"; 



// If user ('Adam') is coming to page for the first time, generate the authorization url
// and redirect him to it.
if( empty($_GET) && empty($_POST)){
	$AUTH_URI="$AUTH_ENDPOINT?type=web&client_id=$CLIENT_ID&redirect_uri=$RETURN_URL&response_type=code";
	header("Location: $AUTH_URI",TRUE,302);
}




// If user ('Adam') is returning from authorization endpoint, then parameter 'code' is on the
// the RETURN_URL value.  We will use it to make a (server-side) cURL request for the access_token.
if(!empty($_GET['code'])){
$CODE = $_GET['code'];

$TOKEN_REQUEST_DATA=array("grant_type"=>"authorization_code",
			  "type"=>"web_server",
			  "client_id"=>$CLIENT_ID,
			  "client_secret"=>$CLIENT_SECRET,
			  "redirect_uri"=>$RETURN_URL,
			  "code"=>$CODE
		);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $TOKEN_REQUEST_ENDPOINT);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$TOKEN_REQUEST_DATA);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
unset($ch);

// Output from server is json formatted, PHP will turn it into an object for us.
$TOKEN_REQUEST_RESPONSE = json_decode($output);
$TOKEN = urlencode($TOKEN_REQUEST_RESPONSE->access_token);

// Build our API endpoint request with the token we've received.
$FULL_API_REQUEST="$API_REQUEST?access_token=$TOKEN";

// Make our server-side cURL call to the endpoint and get JSON back 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $FULL_API_REQUEST);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
unset($ch);

$API_RESPONSE = $output;

echo "<pre>";
echo "\nURL: $FULL_API_REQUEST\n";
print_r(json_decode($API_RESPONSE));
echo "</pre>";

}

?>
