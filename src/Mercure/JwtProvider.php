<?php 
namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Hmac\Sha256;


final class JwtProvider 
{
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }
    
    public function __invoke()
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText('mercure_secret_key'));
        return ($config->builder())
            ->withClaim('mercure', ['publish' => ['*']])
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }
}