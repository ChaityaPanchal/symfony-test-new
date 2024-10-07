<?php

declare(strict_types=1);

namespace App\Controller;

// use Endroid\QrCode\Builder\Builder;
// use Endroid\QrCode\Encoding\Encoding;
// use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
// use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
// use Endroid\QrCode\Writer\PngWriter;
// use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
// use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
// use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
            if($user->getTotpToken() === null){
                $totpToken = $this->totpAuthenticatorInterface->generateSecret();
                $session->set('totpToken',$totpToken);
                $user->setTotpToken($totpToken);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } else{
               $totpToken = $session->get('totpToken');
            }
            $qrCodeContent = $this->totpAuthenticatorInterface->getQRContent($user);

            $result = $this->builderInterface
                ->data($qrCodeContent)
                ->build();
            // dd($result);
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
        // if($user instanceof User){
            $code = (string) $request->request->get('authCode');
            $totpToken = $session->get('totpToken');
            // dd($user);
            // dd($totpToken);
            // $user->setTotpToken($totpToken);
            $isAuthenticate = $this->totpAuthenticatorInterface->checkCode($user, $code);
            // dd($totpToken);
            
            if ($isAuthenticate) {
                $hashPassword = $session->get('hashPassword');
                $user->setPassword($hashPassword);
                // $user->setTotpToken(null);
                $user->setCreatedAt(new \DateTimeImmutable());
                $session->remove('totpSecret');
                $session->remove('hashPassword');

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_welcome');
            }
            else{
                return $this->render('2fa/verify.html.twig');
            }
        // }
        // return $this->redirectToRoute('app_login');
    }
}