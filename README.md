#Naija Emojicon
[![Build Status](https://travis-ci.org/andela-gjames/Emoji-API.svg?branch=develop)](https://travis-ci.org/andela-gjames/Emoji-API)
[![StyleCI](https://styleci.io/repos/48481296/shield)](https://styleci.io/repos/48481296)

Naija Emojicon is a RESTFUL API for creating, retrieving and manipuling Emojis

##Usage
Below are the stages involved in using the package

#####Creating new User
Send a `post` request to `http://base_uri/signup`  with `username` and `password` as data

```curl
curl -i -X POST -H 'Content-Type: application/json' -d '{"username": "test-user", "password": "test-password"}' http://api-emojicon-staging.herokuapp.com/signup
```


#####Login
Send a `post` request to `http://base_uri/auth/login`  with `username` and `password` as data, the response will be a `token` if `username` is not already taken

```curl
curl -i -X POST -H 'Content-Type: application/json' -d '{"username": "test-user", "password": "test-password"}' http://api-emojicon-staging.herokuapp.com/auth/login
```


#####Adding new Emoji
`post` request to `http://base_uri/emojis` with `token` in header and data in the body of the request.

```curl
curl -i -X POST -H 'Content-Type: application/json' -d 
 '{
    "name": "Happy Face",
    "char": ")",
    "keywords": [
      "happy"
    ],
  "category": "Happy"
}' 
https://api-emojicon-staging.herokuapp.com/emojis
```

#####Updating an Emoji
`put` request to `http://base_uri/emojis/{id of emoji}`, with `token` in header and new data in the body of the request
```curl
curl -i -X PUT -H 'Content-Type: application/json' -d 
 '{
    "name": "New Happy Face",
    "char": ")",
    "category": "Happy"
}' 
https://api-emojicon-staging.herokuapp.com/emojis/{id}
```
#####Deleting an Emoji
`delete` request to `http://base_uri/emojis/{id of emoji}`, with `token` in header
```curl
curl -i -X DELETE -H 'Content-Type: application/json' http://api-emojicon-staging.herokuapp.com/emojis/{id}
```

#####Getting all Emojis
Send a `get` request to `http://base_uri/emojis/`
```curl
curl -i -X GET http://api-emojicon-staging.herokuapp.com/emojis
```
#####Getting an Emoji
Send a `get` request to `http://base_uri/emojis/{id of emoji}`
```curl
curl -i -X GET http://api-emojicon-staging.herokuapp.com/emojis/1
```

###Contributing

This is an open-source project, I will be glad if you find time to contribute to make it better.
