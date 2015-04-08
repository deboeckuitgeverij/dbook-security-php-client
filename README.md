dbook-security-php-client
=========================
---

# Summary

A PHP Client which provides access to DeBoeck SSO server.

It is the basic version which can be combined with SAML2.0 protocol if needed.
An OAuth2.0 access is also available.

A basic configuration needs :
* The CDSSO server url environment :
    * https://test-dbook-security.deboeck.com/cdsso/V1/cdsso for test
    * https://preprod-dbook-security.deboeck.com/cdsso/V1/cdsso for pre-production
    * https://dbook-security.deboeck.com/cdsso/V1/cdsso for production
* The broker code
* The broker password

All brokers under the same SSO group will be automaticaly logged-in / logged-out.
Available for the browser session only.

# To DO
* Add autologin capabilities

# Installation

## via composer

```
    "require": {
        "DeBoeck/dbook-security-php-client": "1.*"
    },
```

# Versions

* 1.0.7 - 08/04/2015
    * Objects as models
    * tokens (take/free) 


* 1.0.0 - 27/03/2015

---
Copyright DeBoeck Digital 2015
