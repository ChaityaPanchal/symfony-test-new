<?php

declare(strict_types=1);

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
        private readonly TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $session = $request->getSession();
        if ($user->isEnabled()) {
            if($user->isEnforceResetPassword() && $user->isEnforceTotpAuth() ){
                if ($user->getResetPasswordToken() == null) {
                    return new RedirectResponse($this->router->generate('set_password'));
                }
                if ($user->getTotpToken()) {
                    return new RedirectResponse($this->router->generate('qr_code_generate'));
                }
            }else{
                    return new RedirectResponse($this->router->generate('app_welcome'));
                }
        }
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
