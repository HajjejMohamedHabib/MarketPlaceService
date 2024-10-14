<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin=new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@gmail.com');
        $admin->setPassword(password_hash('password', PASSWORD_DEFAULT));
        $admin->setPhoneNumber('26786647');
        $admin->setAddress('gabes');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();
    }
}
