#!/usr/bin/env python
import urllib,simplejson,cgi

## Set some values we'll be needing through the process
CLIENT_ID="xxxx" # Issued by FormAssembly host
CLIENT_SECRET="xxxx" # Issued by FormAssembly host
RETURN_URL="https://localhost/" # We're doing commandline so this is pointless
AUTH_ENDPOINT="https://xxxxxx/oauth/login" # Replace xxxxxx with correct url
TOKEN_REQUEST_ENDPOINT="https://xxxxxx/oauth/access_token" # Replace xxxxxx with correct url

## API Endpoint to test
## https://server/api_v1/forms/index.xml
API_REQUEST="https://xxxxxx/api_v1/forms/index" 

## Build our authorization endpoint to display to user ('Adam')
AUTH_URI=AUTH_ENDPOINT+"?type=web&client_id="+CLIENT_ID+"&redirect_uri="+RETURN_URL+"&response_type=code"

## Since we're on the commandline, display authorization url to user ('Adam').
print "Go to URL: "+AUTH_URI
print "When directed to https://localhost/?code=XXXXXXXXXX copy and paste code here:\n"

code = raw_input("CODE>")
#Decode any URL encoded values
code = cgi.parse_qs("code="+code)["code"][0]

## We've got the code value, so we can now generate the server-side request for an access_token
TOKEN_REQUEST_DATA=urllib.urlencode({"grant_type":"authorization_code","type":"web_server","client_id":CLIENT_ID,"client_secret":CLIENT_SECRET,"redirect_uri":RETURN_URL,"code":code})

token_request_results=urllib.urlopen(TOKEN_REQUEST_ENDPOINT,TOKEN_REQUEST_DATA)
data = token_request_results.read()
print data

## Let's parse the response into an object from the json response, and grab the access_token
token=simplejson.loads(data)["access_token"]

format = raw_input("What format would you like the form list in? (json,xml,plist)> ")

## Make another server-side request for the API endpoint, using the access_token
FULL_API_REQUEST=API_REQUEST+"."+format+"?"+urllib.urlencode({"access_token":token})
api_response=urllib.urlopen(FULL_API_REQUEST)

## Display the output
print "\nURL:"+FULL_API_REQUEST+"\n"
for line in api_response:
  print line
