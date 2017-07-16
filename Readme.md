# Instagram EEL Helper

## TL;DR

1. Install the package: `composer require codeq/instagram`
2. Go to https://www.instagram.com/developer/ and create a new app
3. Go to https://www.instagram.com/developer/authentication/ and generate an access token. Copy that token to your `Settings.yaml` file in the following way:

```
CodeQ:
  Instagram:
    accessToken: XXX
```

That's all! Now you can use the ready-made `CodeQ.Instagram:InstagramGallery` TS object, e.g.:

```
instagram = CodeQ.Instagram:InstagramGallery {
		count = 6
	}
```

To adjust the looks, alter the `CodeQ.Instagram:Instagram` TS object. It has `data` context variable with all of the fields described here: https://www.instagram.com/developer/endpoints/users/#get_users_media_recent_self

Or you can use the EEL helper directly to make any kind of GET request to the Instagram API, e.g.:
`${Twitter.getRequest('media/search', '?lat=48.858844&lng=2.294351')}`

The EEL helper takes two arguments: Instagram API GET endpoint name and GET arguments for that endpoint.

**The development of this plugin was kindly sponsored by [CODE Q](https://www.codeq.at/)**

<img src="codeq.png" alt="Code Q" width="200"/>
