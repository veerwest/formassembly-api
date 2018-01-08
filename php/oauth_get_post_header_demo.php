<?php
// Set our API Endpoint
// https://server/api_v1/forms/index.xml
// Replace xxxxxx with correct url
$API_REQUEST="https://xxxxxx/api_v1/forms/index.json";
// Set our Access Token (see script fa_api.php to generate an access token)
$TOKEN="xxxxxx";

/*
 * Using GET make API request with access token
 */
// Build our API endpoint request with the token we've received.
    $FULL_API_REQUEST="$API_REQUEST?access_token=$TOKEN";
// Make our server-side cURL call to the endpoint and get JSON back

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $FULL_API_REQUEST);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    unset($ch);
    $API_RESPONSE = $output;
    echo "<pre>";
    echo "\nAUTHORIZE BY GET URL: $FULL_API_REQUEST\n";
    print_r($API_RESPONSE);
    echo "</pre>";
// -------------------------

/*
 * Using POST make API request with access token
 */
// Build our API endpoint request with the token we've received.
$FULL_API_REQUEST="$API_REQUEST";
$TOKEN_DATA = ["access_token"=>$TOKEN];
// Make our server-side cURL call to the endpoint and get JSON back
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $FULL_API_REQUEST);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$TOKEN_DATA);

$output = curl_exec($ch);
unset($ch);
$API_RESPONSE = $output;
echo "<pre>";
echo "\nAUTHORIZE BY POST URL: $FULL_API_REQUEST\n";
print_r($API_RESPONSE);
echo "</pre>";
// -------------------------

// Build our API endpoint request with the token we've received.
$FULL_API_REQUEST="$API_REQUEST";
// Make our server-side cURL call to the endpoint and get JSON back
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $FULL_API_REQUEST);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization:' . sprintf('Token ="%s"', $TOKEN),
]);

$output = curl_exec($ch);
unset($ch);
$API_RESPONSE = $output;
echo "<pre>";
echo "\nAUTHORIZE BY HEADERS URL: $FULL_API_REQUEST\n";
print_r($API_RESPONSE);
echo "</pre>";
?>