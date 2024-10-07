<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('user_1234');
        $user->setEmail('user@gmail.com');
        $user->setRole(UserRole::ADMIN);
        $password = $this->passwordHasher->hashPassword($user, 'user@1234');
        $user->setPassword($password);
        $manager->persist($user);
        $manager->flush();
    }
}
