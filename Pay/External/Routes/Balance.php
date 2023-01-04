<?php

namespace Yep\Pay\External\Routes;

use Yep\Pay\External\Contracts\RouteInterface;

class Balance implements RouteInterface
{

    public static function root()
    {
        return '/balance';
    }

    public static function getList()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::GET_METHOD,
            RouteInterface::ENDPOINT_KEY => Balance::root(),
        ];
    }
}
