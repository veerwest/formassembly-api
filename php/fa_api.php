<?php
/**
 * @see https://packagist.org/packages/fathershawn/oauth2-formassembly
 *
 * Install using composer: composer require fathershawn/oauth2-formassembly
 */

use Fathershawn\OAuth2\Client\Provider\FormAssembly;

// Values we'll need through the process
$CLIENT_ID=htmlspecialchars("xxxx"); // Issued by FormAssembly host
$CLIENT_SECRET=htmlspecialchars("xxxx"); // Issued by FormAssembly host
// Auto generate our return url for wherever this page is located.
$RETURN_URL= (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$BASE_URL="https://xxxxxx/"; // The FormAssembly host.

// Set our API Endpoint
// https://server/api_v1/forms/index.xml
// Replace xxxxxx with correct url
$API_REQUEST="https://xxxxxx/api_v1/forms/index.json"; 



// If user ('Adam') is coming to page for the first time, generate the authorization url
// and redirect him to it.
if( empty($_GET) && empty($_POST)){
  $provider = new FormAssembly([
    'clientId' => $CLIENT_ID,
    'clientSecret' => $CLIENT_SECRET,
    'redirectUri' => $RETURN_URL,
    'baseUrl' => $BASE_URL,
  ]);
  $AUTH_URI = $provider->getAuthorizationUrl();
	header("Location: $AUTH_URI",TRUE,302);
}




// If user ('Adam') is returning from authorization endpoint, then parameter 'code' is on the
// the RETURN_URL value.  We will use it to make a (server-side) cURL request for the access_token.
if(!empty($_GET['code'])){
$CODE = $_GET['code'];

// Try to get an access token using the authorization code grant.
try {
  $accessToken = $provider->getAccessToken('authorization_code', [
 	'code' => $code,
  ]);
} catch (\Exception $e) {
  // Log the error.
  throw $e;
}
$TOKEN = urlencode($accessToken->getToken());

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
