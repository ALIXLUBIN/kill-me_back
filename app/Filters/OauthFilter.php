<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use \App\Libraries\Oauth;
use \OAuth2\Request;

use \OAuth2\Response;


class OauthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {

      helper('cookie');

      if (!$user_id = $this->verifyToken(get_cookie('access_token'))) {
        die(json_encode(['messages' => 'Unauthenticated']));
      }

      $GLOBALS['user_id'] = $user_id;
    }

    public function verifyToken($token) {

      $oauth = new Oauth();
      $request = Request::createFromGlobals();
      if (isset($token))
        $request->headers['AUTHORIZATION'] = 'Bearer ' . $token;
      
      $response = new Response();

      if (!$oauth->server->verifyResourceRequest($request)) {
        $oauth->server->getResponse()->send();
        return false;
      }

      return $oauth->server->getAccessTokenData($request)['user_id'];
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
        // Do something here
    }
}