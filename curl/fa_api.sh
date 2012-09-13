#!/bin/bash

## Constants that we'll need throughout the code.
CLIENT_ID="xxxx" # Issued by FormAssembly host
CLIENT_SECRET="xxxx" # Issued by FormAssembly host
RETURN_URL="https://localhost/"
AUTH_ENDPOINT="https://xxxxxx/oauth/login" # Replace xxxxxx with correct url
TOKEN_REQUEST_ENDPOINT="https://xxxxxx/oauth/access_token" # Replace xxxxxx with correct url

## Set the endpoint
## https://server/admin/api_v1/forms/index.xml
API_REQUEST="https://xxxxxx/api_v1/forms/index" # Replace xxxxxx with correct url



##### Start Addon to handle urlencoding parameters for GET request
##### Bash doesn't have this by default
##### http://stackoverflow.com/questions/296536/urlencode-from-a-bash-script/10660730
urlencode () {
        tab="`echo -en "\x9"`"
        i="$@"
        i=${i//%/%25}  ; i=${i//' '/%20} ; i=${i//$tab/%09}
        i=${i//!/%21}  ; i=${i//\"/%22}  ; i=${i//#/%23}
        i=${i//\$/%24} ; i=${i//\&/%26}  ; i=${i//\'/%27}
        i=${i//(/%28}  ; i=${i//)/%29}   ; i=${i//\*/%2a}
        i=${i//+/%2b}  ; i=${i//,/%2c}   ; i=${i//-/%2d}
        i=${i//\./%2e} ; i=${i//\//%2f}  ; i=${i//:/%3a}
        i=${i//;/%3b}  ; i=${i//</%3c}   ; i=${i//=/%3d}
        i=${i//>/%3e}  ; i=${i//\?/%3f}  ; i=${i//@/%40}
        i=${i//\[/%5b} ; i=${i//\\/%5c}  ; i=${i//\]/%5d}
        i=${i//\^/%5e} ; i=${i//_/%5f}   ; i=${i//\`/%60}
        i=${i//\{/%7b} ; i=${i//|/%7c}   ; i=${i//\}/%7d}
        i=${i//\~/%7e} 
        echo "$i"
        i=""
}
##### End Addon


## Create the authorization url that the user ( 'Adam' ) needs to go to grant your application permission
AUTH_URI="$AUTH_ENDPOINT?type=web&client_id=$CLIENT_ID&redirect_uri=$RETURN_URL&response_type=code"

## Just display the url to the user since we're doing this on the command line
echo "Go to URL: $AUTH_URI"
echo -e "When directed to https://localhost/?code=XXXXXXXXXX copy and paste code here:\n"
read -p "code>" CODE

## Use the code received above to generate the server-side access token request
TOKEN_REQUEST_DATA="grant_type=authorization_code&type=web_server&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET&redirect_uri=$RETURN_URL&code=$CODE"

## Use curl to make the server-side request
TOKEN_RESULTS="`curl --silent --show-error --data $TOKEN_REQUEST_DATA --request POST $TOKEN_REQUEST_ENDPOINT`"
echo -e "\n$TOKEN_RESULTS"
## Do some string manip crazyness to pull the access_token value out of the json response.
##No json parser in bash.
TOKEN="`echo $TOKEN_RESULTS | grep -o \"\\"access_token\\":\\"[a-zA-Z0-9\\\/\+\%]\+\\"\" | sed \"s/\\"access_token\\"://i\" | sed "s/\\"//g\" | sed \"s/\\\\\\\\\//\//g\"`"
TOKEN="`urlencode $TOKEN`"
echo -e "Token: $TOKEN\n"

read -p "What format would you like the form list in? (json,xml,plist)> " FORMAT

## We've got all the stuff we need now.  Toss the token onto the URL and make the API endpoint request.
FULL_API_REQUEST="$API_REQUEST.$FORMAT?access_token=$TOKEN"
API_RESPONSE="`curl --silent --request GET $FULL_API_REQUEST`"

echo -e "\nURL:$FULL_API_REQUEST\n"
echo $API_RESPONSE
