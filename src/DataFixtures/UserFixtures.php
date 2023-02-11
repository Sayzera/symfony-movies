<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {

        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles]) {
            $user = new User();
            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setVimoeApiKey($api_key);
            $user->setRoles($roles);
            $manager->persist($user);
        }


        $manager->flush();
    }


    private function getUserData(): array
    {
        return [
            ['John', 'Wayne', 'test@admin.com', '123456', 'hjd8dehdh', ['ROLE_ADMIN']],
            ['John', 'Wayne2', 'test2@admin:com', '123456', null, ['ROLE_ADMIN']],
            ['John', 'Doe', 'test@user.com', '1234567', null, ['ROLE_USER']]
        ];
    }
}
