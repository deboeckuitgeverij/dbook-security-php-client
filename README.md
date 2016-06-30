dbook-security-php-client
=========================
---

# Summary

A PHP Client which provides access to DeBoeck SSO server and API functions.

It is the basic version which can be combined with SAML2.0 protocol if needed.
An OAuth2.0 access is also available.

A basic configuration needs :
* The DeBoeck server environment : test / pre-production / production
* A broker key
* A broker secret

To ask for a broker, please go to this url : https://dbook-security.deboeck.com/help/broker/ask
All brokers under the same group (not only domain) will be automaticaly logged-in / logged-out.
Available for the browser session only.

# Installation

## Using composer

Add this to your *composer.json*
```
    "repositories": [
        { "type": "git", "url": "https://github.com/deboeckuitgeverij/dbook-security-php-client.git" }
    ],
    "require": {
        "DeBoeck/dbook-security-php-client": "2.*"
    },
```

# Getting started

> All demos [here](https://github.com/DeBoeck/dbook-security-php-sample)

> The models [here](https://github.com/deboeck/dbook-security-php-client/tree/master/src/DBookSecurityClient/Models)

## Basic instantiation

There are no autoloader class provided. You can use the composer. 

```
require_once(APP_PATH . "/vendor/autoload.php");
```

Then easy to get the main Gate. Three paramaters are required :
* the broker key and the broker secret given by DeBoeck.
* the environment
    * ENV_TEST
    * ENV_PREPROD
    * ENV_PROD  

```
/**
 * Instantiate the client, choose the right gate (Standard, OAuth2, Saml2)
 */
 
use DBookSecurityClient\Api;
use DBookSecurityClient\Gate\SSO;

$api = new Api(new SSO('<broker>', '<secret>', SSO::ENV_DEV));
```
> The broker are by default the same for all environments, but they can be different if needed.

## Only for the SSO Gate 

You need the SSO basic knowledge to go thru this part.
> Read the [guide](https://github.com/DeBoeck/dbook-security-guide)

> Sample code [here](https://github.com/DeBoeck/dbook-security-php-sample/tree/master/deboeck/broker2)

### Get logged-in User

```
if (!$api->isAuthenticated()) {
    $user = $api->authenticate(array('login' => $_POST['username'], 'password' => $_POST['username']));
} else {
    $user = $api->getUser();
}
```
> The *$user* variable is a model(class). You can see the structure in the /src/DBookSecurityClient/Models folder.
>
> A user is composed of
> * User main data

### Logout the user

```
$gate->logout();

```

## Only for the OAuth2 Gate 

You need the OAuth 2.0 basic knowledge to go thru this part.
> Read the [guide](https://github.com/DeBoeck/dbook-security-guide)

> Sample code [here](https://github.com/DeBoeck/dbook-security-php-sample/tree/master/deboeck/oauth2)

The redirect url must be given at register time. It can't be changed.
A state parameter is also available for security and/or intern requirements.

### Authorization token

This is an interactive operation between the user and your website.
* The user will be sent to DeBoeck gate to authenticate if not allready done.
* He must authorize access in order to get a authorization token.
* The user will then be redirected to the "redirect uri" mentionned.
* You are also "authorize" to retrieve a token with the authorization token.

You can add a state parameter, send back at callback. The first parameter is an array of scopes, not implemented yet.

```
$api = new Api(new OAuth2('<broker>', '<secret>', OAuth2::ENV_DEV, '<redirect_uri>'));
$gate = $api->getGate();
$code  = $gate->getAuthorizationCode();
```
> This authorization code can be used to retrieve an access token for the authorized resources.
> By default a *GET* parameter called *code* is used at callback
>
> The token has a validity (set at register time)

### Access token

Get an access token for the user.

```
$token = $gate->getOAuth2Token($code);
```

> This token is used as credential for the user. In most case to retrieve user's informations or to get remote resources.
> 
> The token has a validity (set at register time)

### Refresh token

Get a fresh token from a refreshToken (retrieved with getToken function)

```
$token = $api->getOAuth2FreshToken($refreshToken);
```

# Technical informations

# To do

* Add autologin capabilities
* Add interfaces for models
* Refactor base class of Client and Gate

# Versions

* 1.2.2 - 18/09/2015
    * Bug in RedirectTo

* 1.2.1 - 18/09/2015
    * Bug in Site.php

* 1.2.0 - 17/09/2015
    * API : getOAuth2FreshToken
    * UserModel : Sites and Products
    * Documentation

* 1.1.10 - 14/09/2015
    * API getOAuth2Token with state parameter

* 1.1.9 - 14/09/2015
    * OAuth2 askAuthorizationCode with state parameter

* 1.1.8 - 18/06/2015
    * Package informations update

* 1.1.7 - 17/06/2015
    * Packagist hook

* 1.1.6 - 17/06/2015
    * First real release

* 1.1.5 - 17/06/2015
    * Separate gates for oauth, saml, ...
    * User products and sites available if exists 

* 1.1.4 - 15/06/2015
    * adaptations for login and token. API 0.1.3

* 1.1.3 - 15/06/2015
    * test and preprod env adaptations
    * No https in development

* 1.1.2 - 14/06/2015
    * New architecture
    * New models

* 1.1.1 - 13/06/2015
    * OAuth2 Gate

* 1.1.0 - 08/04/2015
    * Objects as models
    * tokens (take/free) 

* 1.0.0 - 27/03/2015

---
Copyright DeBoeck Digital 2015