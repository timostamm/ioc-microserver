<?php
/**
 * Created by PhpStorm.
 * User: ts
 * Date: 19.04.18
 * Time: 07:36
 */

namespace TS\Web\Microserver\Controller;


use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleControllerInvoker implements ControllerInvokerInterface
{

    public function invoke(callable $controller, Request $request, Response $response): Response
    {
        $args = [$request, $response];
        foreach ($request->attributes->get('_route_params', []) as $name => $value) {
            $args[] = $value;
        }

        try {

            $response = call_user_func_array($controller, $args);

        } catch (\Error $error) {
            throw new LogicException(get_class($error) . ' during controller invocation: ' . $error->getMessage(), 0, $error);
        }

        if (!$response instanceof Response) {
            if ($request->attributes->has('_controller')) {
                $msg = sprintf('The controller %s did not return a %s instance.', $request->attributes->get('_controller'), Response::class);
            } else if ($request->attributes->has('_route')) {
                $msg = sprintf('The route %s did not return a %s instance.', $request->attributes->get('_route'), Response::class);
            }
            throw new LogicException($msg);
        }
        return $response;
    }
}