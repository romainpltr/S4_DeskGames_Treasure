<?php 
namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\HttpFoundation\Cookie;

class CookieGenerator
{
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function generate(): Cookie
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText('mercure_secret_key'));
        $token = ($config->builder()
            ->withClaim('mercure', ['subscribe' => ['*']])
            ->getToken($config->signer(), $config->signingKey())
        ->toString());

        return Cookie::create('mercureAuthorization', $token, 0, '/.well-known/mercure');
    }
}