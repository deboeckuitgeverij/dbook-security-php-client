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
    "require": {
        "DeBoeck/dbook-security-php-client": "1.*"
    },
```

# Getting started

> All demos [here](https://github.com/DeBoeck/dbook-security-php-sample)

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
 * Instantiate the client, choose the right gate
 */
$gate = \DBookSecurityClient\StandardAuthGate::getInstance(
    '<broker>',
    '<scret>',
    \DBookSecurityClient\Constants::ENV_PREPROD
);
$gate->setRedirectUri('<My callback url>');
```
> The broker are by default the same for all environments, but they can be different if needed.

## Only for the StandardAuth Gate 

You need the SSO basic knowledge to go thru this part.
> Read the [guide](https://github.com/DeBoeck/dbook-security-guide)

> Sample code [here](https://github.com/DeBoeck/dbook-security-php-sample/tree/master/broker2)

### Get logged-in User

```
/**
 * Get connected user, if none go to login
 */
$user = $gate->getUser();
if (!$user) {
    header("Location: login.php", true, 307);
    exit;
}
```
> The *$user* variable is a model(class), products ans sites to. You can see the structure in the /src/DBookSecurityClient/Models folder.
>
> A user is composed of
> * User main data
> * User products
> * The DeBoeck websites of the user

### Login a user

```
/**
 * Login requested
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $gate->signinByLoginAndPassword($_POST['username'], $_POST['password'])) {
   header("Location: index.php", true, 303);
   exit;
} else {
   // handle error
}
```

### Logout the user

```
$gate->logout();

```

## Only for the OAuth2 Gate 

You need the OAuth 2.0 basic knowledge to go thru this part.
> Read the [guide](https://github.com/DeBoeck/dbook-security-guide)

> Sample code [here](https://github.com/DeBoeck/dbook-security-php-sample/tree/master/oauth2)

### Authorization token

This is an interactive operation between the user and your website. The user will be sent to DeBoeck gate to authenticate if not allready done. Then he must authorize access. You will then get on the redirect uri mentionned the authorization code.

```
$gate->askAuthorizationCode();
exit; // It' a redirect
```
> This authorization code can be used to retrieve an access token for the authorized resources.
> By default a *GET* parameter called *code* is used at callback
>
> The token has a validity (set at register time)

### Access token

Get an access token for the user.

```
$token = $gate->getToken($code);
```

> This token is used as credential for the user. In most case to retrieve user's informations or to get remote resources.
> 
> The token has a validity (set at register time)

# Technical informations

All methods are available in the /src/DBookSecurityClient/Interfaces folder.

[Here](https://github.com/DeBoeck/dbook-security-guide/blob/master/api/readme.md) you can find a more detailed version. It's the DeBoeck api documentation.

# To do

* Add autologin capabilities
* Add interfaces for models
* Refactor base class of Client and Gate


# Versions

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