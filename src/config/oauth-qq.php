<?php
//qq授权登陆 
return   [
    
	'qq' => [
        'client_id' => env('QQ_KEY'),
        'client_secret' => env('QQ_SECRET'),
        'redirect' => env('QQ_REDIRECT_URI')
    ],
	
	
	
];
