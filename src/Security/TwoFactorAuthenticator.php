<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class TwoFactorAuthenticator
{
    public function __construct(private GoogleAuthenticator $googleAuthenticator)
    {
    }

    public function generateSecret(): string
    {
        return $this->googleAuthenticator->generateSecret();
    }

    public function getQRCodeUrl(User $user): string
    {
        $issuer = 'TOTP Authentication';
        $qrCodeUrl = $this->googleAuthenticator->getUrl($user->getUsername(), $user->getTotpToken(), $issuer);
        return $qrCodeUrl;
    }

    public function checkCode(string $secret, string $code): bool
    {
        return $this->googleAuthenticator->checkCode($secret, $code);
    }
}
