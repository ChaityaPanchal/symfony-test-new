<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\SetPasswordFormType;
use App\Security\TwoFactorAuthenticator;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TwoFactorAuthenticator $twoFactorAuthenticator,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();
            $username = $user->getUsername();

            $existingUserByEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            $existingUserByUsername = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if ($existingUserByUsername) {
                $this->addFlash('registration_error', 'Username is already taken.');
            } elseif ($existingUserByEmail) {
                $this->addFlash('registration_error', 'Email is already registered.');
            } elseif (strlen($user->getUsername()) <= 6) {
                $this->addFlash('registration_error', 'Username must more than 6 character with (alphbets, digits and sepical charater).');
            } elseif (preg_match('/^(?=.*[a-z])(?=.*\\d)(?=.*[-+_!@#$%^&*.,?]).+$/', $user->getUsername()) == 0) {
                $this->addFlash('registration_error', 'Username have alphabets, digits and sepical charater.');
            } else {
            $uniquePassword = bin2hex(openssl_random_pseudo_bytes(10));
            $hashedPassword = $passwordHasher->hashPassword($user, $uniquePassword);

            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $emailLink = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $email = (new TemplatedEmail())
                ->from('yash1234@gmail.com')
                ->to($user->getEmail())
                ->subject('1st login')
                ->html('<p>Your current password is <b>'. $uniquePassword .'</b></p> Now login toyour account
                        <p><a href="' . $emailLink . '">Click here</a></p>');
            $this->mailer->send($email);
            $this->addFlash('registration_success', 'Registration is successfully done. Now, please check your email and login for set your password');
            return $this->redirectToRoute('app_register');   
        }
    }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    


    #[Route('/set-password', name: 'set_password')]
    public function setPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if($user instanceof User){
        $session = $request->getSession();
        $token = $session->get('uniqueToken');  
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['resetPasswordToken' => $token]);
        $form = $this->createForm(SetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $conPassword = $form->get('con_password')->getData();

            if ($password != $conPassword){
                $this->addFlash('set_password_error', "Confim password doesn't match");
            } elseif ($user->getUsername() === $password){
                $this->addFlash('set_password_error', "username & password could not same.");
            } elseif (strlen($password) <= 8) {
                $this->addFlash('set_password_error', 'Password must more than 8 character.');
            } elseif (preg_match('/^(?=.*[a-z])(?=.*\\d)(?=.*[-+_!@#$%^&*.,?]).+$/', $password) == 0) {
                $this->addFlash('set_password_error', 'Password have alphabets, digits and sepical charater.');
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $session->set('hashPassword',$hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // if($user->getCreatedAt()){
            //     return $this->redirectToRoute('app_login');
            // }
            return $this->redirectToRoute('qr_code_generate');
        }
        return $this->render('password/set_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
        return $this->redirectToRoute('app_login');
    }


    #[Route('/login', name: 'app_login')]
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new Exception('logout() should never be reached');
    }

    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(): Response
    {
        $user = $this->getUser();
        // if ($user) {
        //     $this->entityManager->persist($user);
        //     $this->entityManager->flush();
        // }
        return $this->render('welcome.html.twig');
    }
}
