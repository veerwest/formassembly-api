const request = require('request');
const readline = require('readline');
const querystring = require('querystring');

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

// Set some values we'll be needing through the process
const CLIENT_ID="xxxx" // Issued by FormAssembly host
const CLIENT_SECRET="xxxx" // Issued by FormAssembly host
const RETURN_URL="https://localhost/" // We're doing commandline so this is pointless
const AUTH_ENDPOINT="https://xxxx/oauth/login" // Replace xxxxxx with correct url
const TOKEN_REQUEST_ENDPOINT="https://xxxx/oauth/access_token" // Replace xxxxxx with correct urlconst 

// API Endpoint to test
// https://server/api_v1/forms/index.xml
const API_REQUEST="https://xxxx/api_v1/forms/index"

// Build the query string
const API_AUTH_QUERY = {
  type:'web',
  client_id:CLIENT_ID,
  redirect_uri:RETURN_URL,
  response_type:'code'
};

let unescapedQS = querystring.unescape(querystring.stringify(API_AUTH_QUERY, '&', '=', { encode: false }))

// Build our authorization endpoint to display to user ('Adam')
const AUTH_URI=AUTH_ENDPOINT+"?"+unescapedQS;

// Since we're on the commandline, display authorization url to user ('Adam').
console.log("Go to URL: ", AUTH_URI);
console.log("When directed to https://localhost/?code=XXXXXXXXXX copy and paste code here: (do not include the ending #)\n");

rl.setPrompt('CODE> ');
rl.prompt();

rl.on('line', (input) => {
  let code = input;

  let qObj = {grant_type:'authorization_code',type:'web_server',client_id:CLIENT_ID,client_secret:CLIENT_SECRET,redirect_uri:RETURN_URL,code:code};

  let post_data = querystring.unescape(querystring.stringify(qObj));

  request({
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Content-Length': Buffer.byteLength(post_data),
      'Accept': 'application/json'
    },
    url: TOKEN_REQUEST_ENDPOINT, 
    method: 'POST', 
    body: post_data}, (error, response, body) => {
      if (error) {
        console.error(error);
      }
      console.log(response.statusCode);
      console.log(body);

      let token = JSON.parse(body)['access_token'];
      if (token != null) {
        rl.question('What format would you like the form list in? (json,xml,plist)> ', (format) => {

          const FULL_API_REQUEST=API_REQUEST+'.'+format+'?access_token='+token;
          console.log('Calling...', FULL_API_REQUEST);
          request({url: FULL_API_REQUEST, method: 'GET'}, (error, response, body) => {
            if (error) {
              console.error(error);
            }
            console.log(response.statusCode);
            console.log(body);
            rl.close();
          });
        });
      } else {
        rl.close();
      }
    });
});
