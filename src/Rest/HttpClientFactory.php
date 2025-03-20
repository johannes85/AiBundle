<?php

namespace AiBundle\Rest;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientFactory {

  public function __invoke(): HttpClientInterface {
    return HttpClient::create();
  }

}
