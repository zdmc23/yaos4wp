# yaos4wp
Yet Another OAuth2 Server for WordPress (YAOS4WP)

## Installation

0. Create a private key `openssl genrsa -out private.key 2048`
0. Create a public key `openssl rsa -in private.key -pubout > public.key`
0. WordPress likes keys to have a particular permissions: `sudo chmod 600 *.key`
0. Run the following in: `path/to/wp-content/plugins`
```
composer require league/oauth2-server
composer require nyholm/psr7
```

### Testing the authorization request via a Web Browser (it's possible with cURL -L, but you have to sift through response HTML for the code) 

Replace `[YOUR_DOMAIN_OR_IP]` and `[YOUR_PORT]`. Replace the "redirect_uri" parameter with your desired URI (for mobile apps you can register a DeepLink to handle this)

http://[YOUR_DOMAIN_OR_IP]:[YOUR_PORT]/yaos4wp/authorize?response_type=code&redirect_uri=http://[YOUR_DOMAIN_OR_IP]:[YOUR_PORT]/yaos4wp/callback&client_id=myawesomeapp&scope=basic&state=zz


### Testing the 'authorization_code' grant_type example

Send the following cURL request. Replace `[YOUR_DOMAIN_OR_IP]` and `[YOUR_PORT]`. Replace `[CODE]` with the response code from previous authorization request above, and replace `[REDIRECT_URI]` with the same redirect_uri value used in the previous authorization request above:

```
curl -X POST "http://[YOUR_DOMAIN_OR_IP]:[YOUR_PORT]/yaos4wp/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "grant_type=authorization_code" \
  --data-urlencode "code=[CODE]" \
  --data-urlencode "client_id=myawesomeapp" \
  --data-urlencode "redirect_uri=[REDIRECT_URI]"
```

### Testing an authorized resource request

Send the following cURL request. Replace `[YOUR_DOMAIN_OR_IP]` and `[YOUR_PORT]`. Replace `[BEARER_TOKEN]` with the Bearer Token from previous 'authorization_code' grant_type request above:

```
curl  "http://[YOUR_DOMAIN_OR_IP]:[YOUR_PORT]/wp-json/dt/v1/contacts" \
  -H "Authorization: Bearer [BEARER_TOKEN]"
```

### Testing the 'refresh_token' grant_type example

Send the following cURL request. Replace `[YOUR_DOMAIN_OR_IP]` and `[YOUR_PORT]`. Replace `[REFRESH_TOKEN]` with the Refresh Token from previous 'authorization_code' grant_type request above:

```
curl -X POST "http://[YOUR_DOMAIN_OR_IP]:[YOUR_PORT]/yaos4wp/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "grant_type=refresh_token" \
  --data-urlencode "client_id=myawesomeapp" \
  --data-urlencode "refresh_token=[REFRESH_TOKEN]"
```
