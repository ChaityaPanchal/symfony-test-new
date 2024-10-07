<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Endroid\QrCode\Builder\BuilderInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/2fa')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private readonly TotpAuthenticatorInterface $totpAuthenticatorInterface, 
        private readonly BuilderInterface $builderInterface,
        private readonly EntityManagerInterface $entityManager,
    ){}

    #[Route('/qr_code', name: 'qr_code_generate')]
    public function QrCodeGenerate(Request $request) : Response
    {
        $user = $this->getUser();
        $session = $request->getSession();
        if ($user instanceof User) {
             if($session->get('totpToken') == null){
                $totpToken = $this->totpAuthenticatorInterface->generateSecret();
                $user->setTotpToken($totpToken);
                $session->set('totpToken',$totpToken);
            } else{
                $totpToken = $session->get('totpToken');
                $user->setTotpToken($totpToken);
            }
            $qrCodeContent = $this->totpAuthenticatorInterface->getQRContent($user);
            $result = $this->builderInterface
                ->data($qrCodeContent)
                ->build();
            $qrCodeBase64 = base64_encode($result->getString());

            return $this->render('2fa/qr_code.html.twig', [
                'qrCodeBase64' => $qrCodeBase64,
                'totpToken' => $totpToken,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify', name: '2fa_verify')]
    public function QrCodeVerify(Request $request) : Response
    {
        $user = $this->getUser();
        $session = $request->getSession();
            $code = (string) $request->request->get('authCode');
            $totpToken = $session->get('totpToken');
            $user->setTotpToken($totpToken);
            $isAuthenticate = $this->totpAuthenticatorInterface->checkCode($user, $code);
            
            if ($isAuthenticate) {
                $hashPassword = $session->get('hashPassword');
                $user->setPassword($hashPassword);
                $user->setResetPasswordToken(null);
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setTotpToken($totpToken);
                $user->setEnforceResetPassword(false);
                $user->setEnforceTotpAuth(false);
                // dd($user);

                $session->remove('totpToken');
                $session->remove('hashPassword');
                $session->set('enforceResetPassword',false);
                $session->set('enforceTotpToken',false);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_welcome');
            }
            else{
                return $this->render('2fa/verify.html.twig');
            }
    }
}