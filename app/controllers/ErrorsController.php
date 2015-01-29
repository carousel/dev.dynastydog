<?php

class ErrorsController extends BaseController {

    public function get401(Exception $exception, $code)
    {
        // Show the page
        return Response::view('errors/401', [ 'exception' => $exception, 'code' => $code ], $code);
    }

    public function get403(Exception $exception, $code)
    {
        // Show the page
        return Response::view('errors/403', [ 'exception' => $exception, 'code' => $code ], $code);
    }

    public function get404(Exception $exception, $code)
    {
        // Show the page
        return Response::view('errors/404', [ 'exception' => $exception, 'code' => $code ], $code);
    }

    public function getError(Exception $exception, $code)
    {
        // Show the page
        return Response::view('errors/error', [ 'exception' => $exception, 'code' => $code ], $code);
    }

    public function getMaintenance($code)
    {
        View::share(array(
            'layout' => 'frontend/layouts/maintenance', 
        ));

        // Show the page
        return Response::view('errors/maintenance', [], $code);
    }

}
