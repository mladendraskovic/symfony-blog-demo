<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager,'Admin', 'admin@test.com', 'admin12345', [User::ROLE_ADMIN]);
        $this->createUser($manager,'Test User', 'test@test.com', 'test12345', [User::ROLE_USER]);

        $manager->flush();
    }

    private function createUser(
        ObjectManager $manager,
        string $name,
        string $email,
        string $password,
        array $roles = []
    )
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        if (empty($roles)) {
            $roles = [User::ROLE_USER];
        }

        $user->setRoles($roles);

        $manager->persist($user);
    }
}
