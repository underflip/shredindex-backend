<?php

return [

	/*
		    |--------------------------------------------------------------------------
		    | Third Party Services
		    |--------------------------------------------------------------------------
		    |
		    | This file is for storing the credentials for third party services such
		    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
		    | default location for this type of information, allowing packages
		    | to have a conventional place to find your various credentials.
		    |
	*/

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN'),
		'secret' => env('MAILGUN_SECRET'),
		'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
	],

	'mandrill' => [
		'secret' => env('MANDRILL_SECRET'),
	],

	'ses' => [
		'key' => env('AWS_ACCESS_KEY_ID'),
		'secret' => env('AWS_SECRET_ACCESS_KEY'),
		'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
	],

	'sparkpost' => [
		'secret' => env('SPARKPOST_SECRET'),
	],

	'google' => [
		'client_id' => env('GOOGLE_CLIENT_ID'),
		'client_secret' => env('GOOGLE_CLIENT_SECRET'),
		'redirect' => env('GOOGLE_REDIRECT_URI'), // Your OAuth callback route
	],

	'facebook' => [
		'client_id' => env('FACEBOOK_CLIENT_ID'),
		'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
		'redirect' => env('FACEBOOK_REDIRECT_URI'), // Your OAuth callback route
	],

	'x' => [
		'client_id' => env('X_CLIENT_ID'),
		'client_secret' => env('X_CLIENT_SECRET'),
		'redirect' => env('X_REDIRECT_URI'), // Your OAuth callback route
	],

];
