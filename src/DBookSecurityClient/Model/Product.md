# Product
######
---

A product. The list retrieved with $user->getProducts()
All the user's products with expiration date, ...
Use only the ones with a code.

## id
> name : getId
>
> return : string

    In most case the ean, DeBoeck's id.

## name
> name : getName
>
> return : string

    The main title

## code
> name : getCode
>
> string

    The partner specific code

## duration
> name : getDuration
>
> return : \DBooKSecurityClient\Constants::PRODUCT_DURATION_*

## audience
> name : getAudience
>
> return : \DBooKSecurityClient\Constants::AUDIENCE_*

## valid from
> name : getFrom
>
> return : string (date'Y-m-d H:i:s')

## valid until
> name : getTo
>
> return : string (date'Y-m-d H:i:s')

## type
> name : getType
>
> return : \DBooKSecurityClient\Constants::PRODUCT_TYPE_*

## tokens left
> name : getTokens
>
> return : number

## valid ?
> name : isValid
>
> return : boolean

---
Copyright DeBoeck Digital 2015