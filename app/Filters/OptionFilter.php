<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class OptionFilter implements FilterInterface
{
  public function before(RequestInterface $request, $arguments = null)
  {
    header("Access-Control-Allow-Origin: " . env('FRONT_ADDRESSE'));
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Expose-Headers");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Credentials: true");
    if ($request->getMethod() == 'options') {
      die();
    }
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // Do something here
  }
}
