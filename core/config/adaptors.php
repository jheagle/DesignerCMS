<?php

use Core\Adaptors\Vendor\CacheRegistry\CacheRegistry;
use Core\Adaptors\Vendor\CacheRegistry\Contracts\CacheItem;
use Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException;
use Core\Adaptors\Vendor\Curl\Client;
use Core\Adaptors\Vendor\Curl\Exceptions\CurlException;
use Core\Adaptors\Vendor\Curl\Exceptions\RequestException;
use Core\Adaptors\Vendor\Curl\HandlerStack;
use Core\Adaptors\Vendor\Curl\Mocks\Handler;
use Core\Adaptors\Vendor\Curl\Request;
use Core\Adaptors\Vendor\Curl\RequestOptions;
use Core\Adaptors\Vendor\Curl\Response;
use Core\Adaptors\Vendor\Curl\Stream;
use Core\Adaptors\Vendor\Logger\Handlers\StreamHandler;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Adaptors\Vendor\OAuth\Exceptions\IdentityException;
use Core\Adaptors\Vendor\OAuth\Provider;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

return [
    CacheItem::class => ItemInterface::class,
    CacheRegistry::class => FilesystemAdapter::class,
    Client::class => \GuzzleHttp\Client::class,
    HandlerStack::class => \GuzzleHttp\HandlerStack::class,
    Handler::class => MockHandler::class,
    Response::class => \GuzzleHttp\Psr7\Response::class,
    Request::class => \GuzzleHttp\Psr7\Request::class,
    RequestOptions::class => \GuzzleHttp\RequestOptions::class,
    Stream::class => \GuzzleHttp\Psr7\Stream::class,
    StreamHandler::class => \Monolog\Handler\StreamHandler::class,
    Logger::class => \Monolog\Logger::class,
    Provider::class => GenericProvider::class,
    'throwable' => [
        CurlException::class => GuzzleException::class,
        RequestException::class => \GuzzleHttp\Exception\RequestException::class,
        IdentityException::class => IdentityProviderException::class,
        InvalidArgumentException::class => \Psr\Cache\InvalidArgumentException::class,
    ],
];
