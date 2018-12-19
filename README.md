# yaos4wp
Yet Another OAuth2 Server for WordPress (YAOS4WP)

#### Authorize via a Web Browser (it's possible with cURL -L, but you have to sift through response HTML for the code) ####

http://localhost:8000/yaos4wp/authorize?response_type=code&redirect_uri=http://localhost:8000/callback.php&state=zz&client_id=myawesomeapp&scope=basic

(Replace the "redirect_uri" parameter with your desired URI; for Mobile you can register a DeepLink to handle this)

#### GET THE ACCESS TOKEN ####

curl -X POST -H "Content-Type: application/x-www-form-urlencoded" --data-urlencode "grant_type=authorization_code" --data-urlencode "code=[FROM RESPONSE OF AUTHORIZE INVOCATION]" --data-urlencode "client_id=myawesomeapp" --data-urlencode "redirect_uri=[SAME VALUE AS USED IN AUTHORIZE INVOCATION]" "http://localhost:8000/yaos4wp/token"

#### MAKE AN AUTHORIZED RESOURCE REQUEST ####

curl -H "Authorization: Bearer [BEARER TOKEN FROM RESPONSE OF ACCESS TOKEN INVOCATION]" "http://localhost:8000/wp-json/dt/v1/contact/99"
