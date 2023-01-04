<?php

namespace Yep\Pay\External\Http;

use \Yep\Pay\External\Contracts\RouteInterface;
use \Yep\Pay\External\Helpers\Router;
use \Yep\Pay\External\YepPay;

class RequestBuilder
{
    protected $yeppayObj;
    protected $interface;
    protected $request;

    public $payload = [ ];
    public $sentargs = [ ];

    public function __construct($yeppayObj, $interface, array $payload = [ ], array $sentargs = [ ])
    {
        $this->request = new Request($yeppayObj);
        $this->yeppayObj = $yeppayObj;
        $this->interface = $interface;
        $this->payload = $payload;
        $this->sentargs = $sentargs;
    }

    public function build()
    {

    // throw new \Exception(
    //     json_encode(Router::YEPPAY_API_ROOT)
    // );
//$this->interface[RouteInterface::ENDPOINT_KEY]
        $this->request->headers["Authorization"] = "Bearer " . $this->yeppayObj->secret_key;
        $this->request->headers["User-Agent"] = "YepPay/v1 PhpBindings/" . YepPay::VERSION;
        $this->request->endpoint = Router::YEPPAY_API_ROOT . $this->interface[RouteInterface::ENDPOINT_KEY];
        $this->request->method = $this->interface[RouteInterface::METHOD_KEY];
        $this->moveArgsToSentargs();
        $this->putArgsIntoEndpoint($this->request->endpoint);
        $this->packagePayload();
        return $this->request;
    }

    public function packagePayload()
    {
        if (is_array($this->payload) && count($this->payload)) {
            if ($this->request->method === RouteInterface::GET_METHOD) {
                $this->request->endpoint = $this->request->endpoint . '?' . http_build_query($this->payload);
            } else {
                $this->request->body = json_encode($this->payload);
            }
        }
    }

    public function putArgsIntoEndpoint(&$endpoint)
    {
        foreach ($this->sentargs as $key => $value) {
            $endpoint = str_replace('{' . $key . '}', $value, $endpoint);
        }
    }

    public function moveArgsToSentargs()
    {
        if (!array_key_exists(RouteInterface::ARGS_KEY, $this->interface)) {
            return;
        }
        $args = $this->interface[RouteInterface::ARGS_KEY];
        foreach ($this->payload as $key => $value) {
            if (in_array($key, $args)) {
                $this->sentargs[$key] = $value;
                unset($this->payload[$key]);
            }
        }
    }
}
