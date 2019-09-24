# oauth-qq
laravel qq授权 redis缓存方式

## 基本使用

1、 下载包
```bash
composer require wll/oauth-qq
```

2、 app.php 添加 providers
```php
Wll\OauthQq\OauthProvider::class,
```


3、 发布配置文件在config目录下面
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



5、开始使用 - 授权控制器中写
```php

use Illuminate\Http\Request;
use Wll\OauthQq\Facades\Oauth;	

//拉起qq授权

Oauth::oauth($request)
```


6、获取缓存中的授权数据-回调控制器中写
```php

use Illuminate\Http\Request;
use Wll\OauthQq\Facades\Oauth;	


//授权后返回的code 与 state key
$state = $request->input('state');
$code = $request->input('code');

//获取授权qq用户信息
$qqUserInfo=Oauth::getUserInfoByCode($code);

//print_r($qqUserInfo);

//通过state获取qq授权时传的参数
$qqAauthParam = Cache::get('qq:oauth:state:' . $state);
//print_r($qqAauthParam);

//以下就可以写用户注册数据库逻辑......

```
