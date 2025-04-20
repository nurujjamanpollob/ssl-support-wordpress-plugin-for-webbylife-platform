This WordPress plugin offers a simple way to verify ACME verification challenges.

It works for only HTTP challenges, and it is designed to be used with the ACME protocol for obtaining SSL/TLS certificates. 
This plugin is designed to be used by WebByLife utility. 

This plugin won't activate if this plugin cannot listen at get request at /.well-known/acme-challenge/ path.

## How it works

1. The plugin listens for incoming HTTP requests to yoursite/.well-known/acme-challenge/ 
2. When a request is received, it checks if the request is for an ACME challenge.
3. If the request is for an ACME challenge, it retrieves the challenge response from the database.
4. Note that: data is added with generated api key, during generating the certificate in the webbylife utility, and you need to provide the key from plugin settings. You can revoke the api access by
   generating a new key from the plugin settings.
5. If the challenge response is found, it returns the response to the client.
6. If the challenge response is not found, it returns a 404 error.

## Installation
1. Download ssl-support-for-webbylife-platform.zip, upload as plugin from your WordPress admin panel.
2. Activate the plugin.
3. Go to the plugin settings page and generate a new random API key. The key can be 16–512 characters long.
4. Copy the generated key and use it in the WebByLife utility to add the challenge response. WebByLife utility will add the challenge response to the database with the generated key.

## Warning
This plugin is designed to be used with the WebByLife utility. It is not intended for use with other ACME clients or for general-purpose ACME challenge verification.

## Management
1. You can manage the plugin settings from the WordPress admin panel.
2. You can generate a new API key from the plugin settings page.
3. You can revoke the API access by generating a new key.
4. You can see, edit, and delete the challenge responses from the plugin settings page.

## Where to generate certificate
You can generate the certificate using the WebByLife utility. The Utility website is still in development, stay tuned for updates.