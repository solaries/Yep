<?php

namespace Yep\Pay\External\Routes;

use Yep\Pay\External\Contracts\RouteInterface;

class Settlement implements RouteInterface
{

    public static function root()
    {
        return '/settlement';
    }

    public static function getList()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::GET_METHOD,
            RouteInterface::ENDPOINT_KEY => Settlement::root(),
        ];
    }
}
