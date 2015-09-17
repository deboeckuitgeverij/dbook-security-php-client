# User
######
---

The main object, here the maintained methods :

# Identifier

## id
> name : getId
>
> return : string

# Civility

## title
> name : getTitle
>
> return : string

## firstname
> name : getFirstname
>
> return : string

## lastname
> name : getLastname
>
> return : string

# Other identifier

## login
> name : getLogin
>
> return : string

## email
> name : getEmail
>
> return : string

# Others

## language
> name : getPreferredLanguage
>
> return : \DBookSecurityClient\Constants::LANGUAGE_*

    If not set or set with an unknown value, use your default or the browser language.

## products
> name : getProducts
>
> return : array(\DBookSecurityClient\Models\Product)

## sites
> name : getSites
>
> return : array(\DBookSecurityClient\Models\Site)

---
Copyright DeBoeck Digital 2015