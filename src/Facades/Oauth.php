<?php

namespace Wll\OauthQq\Facades;//包里会自动省略src
use Illuminate\Support\Facades\Facade;//设置门面
class Oauth extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'oauth';
	}
}
?>