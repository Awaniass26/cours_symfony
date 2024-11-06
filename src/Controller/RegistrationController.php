<?php

namespace App\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(UserPasswordHasherInterface $passwordHasher): Response
{
    $user = new User('user');
    $plaintextPassword = 'your_password_here';

    // Hash the password
    $hashedPassword = $passwordHasher->hashPassword(
        $user,
        $plaintextPassword
    );
    $user->setPassword($hashedPassword);

    // Retourner un rendu de template (par exemple registration/index.html.twig)
    return new Response('User registered successfully!');
}

}
