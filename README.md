# oauth-qq
laravel qq授权 redis缓存方式

## 基本使用

1. 下载包
```bash
composer require wll/oauth-qq
```

2. app.php 添加 providers
```php
Wll\OauthQq\OauthProvider::class,
```


3. 先发布配置文件在config目录下面
```bash
php artisan vendor:publish
```

4. 修改：config/oauth-qq.php
```php

return   [
    
	'qq' => [
        'client_id' => env('QQ_KEY','QQ互联申请的APP ID'),
        'client_secret' => env('QQ_SECRET','QQ互联申请的key'),
        'redirect' => env('QQ_REDIRECT_URI','QQ互联申请的时配置的回调url')
    ],	
	
];

```



5 开始使用
```php

use Wll\OauthQq\Facades\Oauth;	

//qq授权

$request = new Request();

Oauth::oauth($request)
```
