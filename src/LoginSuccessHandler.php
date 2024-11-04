<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    { {
            // Récupérer l'utilisateur authentifié
            $user = $token->getUser();

            // Vérifier si l'utilisateur est une instance de UserInterface
            if ($user instanceof UserInterface) {
                $roles = $user->getRoles();

                // Vérifier le rôle et rediriger en conséquence
                if (in_array('ROLE_BOUTIQUIER', $roles)) {
                    return new Response('', 302, ['Location' => $this->router->generate('admin_dashboard')]);
                }

                if (in_array('ROLE_VENDEUR', $roles)) {
                    return new Response('', 302, ['Location' => $this->router->generate('vendeur_dashboard')]);
                }
            }

            // Par défaut, rediriger vers la page d'accueil
            return new Response('', 302, ['Location' => $this->router->generate('home')]);
        }
    }
}
