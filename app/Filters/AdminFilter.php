<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use \App\Models\UserModel;

class AdminFilter implements FilterInterface
{
  public function before(RequestInterface $request, $arguments = null)
  {
    $model = new UserModel();

    $scope = $model->getScope($GLOBALS['user_id']);

    if ($scope['scope'] !== 'admin')
      http_response_code(403) &&
      die(json_encode(['messages' => 'Not admin']));

  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // Do something here
  }
}
