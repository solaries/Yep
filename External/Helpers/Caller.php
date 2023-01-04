<?php
namespace Yep\Pay\External\Helpers;

use \Closure;
use \Yep\Pay\External\Contracts\RouteInterface;
use \Yep\Pay\External\Http\RequestBuilder;

class Caller
{
    private $yeppayObj;

    public function __construct($yeppayObj)
    {
        $this->yeppayObj = $yeppayObj;
    }

    public function callEndpoint($interface, $payload = [ ], $sentargs = [ ])
    {
        $builder = new RequestBuilder($this->yeppayObj, $interface, $payload, $sentargs);
        return $builder->build()->send()->wrapUp();
    }
}
