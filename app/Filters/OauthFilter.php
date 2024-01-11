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
       $oauth = new Oauth();
       $request = Request::createFromGlobals();
       $response = new Response();

       if(!$oauth->server->verifyResourceRequest($request)){
         $oauth->server->getResponse()->send();
         die(json_encode(['messages' => 'Unauthenticated']));
       }

       $GLOBALS['user_id'] = $oauth->server->getAccessTokenData($request)['user_id'];

    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
        // Do something here
    }
}