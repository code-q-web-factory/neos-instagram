# BETA: Instagram images list for Neos CMS

This package retrieves instagram media files through the Facebook API to display it. 
Version two implements the new Facebook API, but has some rough edges, so please consider it beta. 

__Please note:__ In this release you still need to manually refresh the token. 
Automatically refreshing of tokens is a bit complex and would only make sense if more people use it. 
If you are start using the package and would like to foster the development please write me at rs@codeq.at

*The development and the public-releases of this package are generously sponsored by [Code Q Web Factory](http://codeq.at).*

## Installation

CodeQ.Instagram is available via packagist, and compatible to the Facebook API in version 2.
Add `"codeq/instagram" : "~2.0"` to the require section of the composer.json or run:

```bash
composer require codeq/instagram
```

We use semantic-versioning so every breaking change will increase the major-version number.

## Usage

1. Create a Facebook Instagram app. Complete steps 1-5 of 
[these Facebook instructions](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started)
until and including "Step 5: Exchange the Code for a Token".
2. Set the Instagram app id as `CodeQ.Instagram.appId` and the app secret as `CodeQ.Instagram.appSecret` in your project configuration
3. Go to https://api.instagram.com/oauth/authorize?client_id={YOUR_APP_ID}&redirect_uri=https://{YOUR_DOMAIN}/instagram-endpoint&scope=user_profile,user_media&response_type=code and run the authorization process. Make sure that the redirect uri is set as such in your app settings on Facebook!


## How to render images

	<CodeQ.Instagram:ImagesList
		attributes.class="instagram-images-list instagram-images-list--images-per-row-3"
		limit="12"
		/>

By default, we do not include any styling. Feel free to include `Resources/Private/Fusion/InstagramList.scss` 
into your own build process are create any custom styling.

## License

Licensed under MIT, see [LICENSE](LICENSE)

## Contribution

We will gladly accept contributions. Please send us pull requests.

---------

<img src="codeq.png" alt="Code Q" width="200"/>
