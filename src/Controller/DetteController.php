<?php

namespace App\Controller;

use App\Entity\Client;


use App\Entity\Dette;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class DetteController extends AbstractController
{
    private $paginator; 

    public function __construct(PaginatorInterface $paginator) 
    {
        $this->paginator = $paginator;
    }


    #[Route('/dette/{clientId}', name: 'app_dette')]
    public function index($clientId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $dette = new Dette();
        
        $dette->setVirtualDate(new \DateTime());
        $client = $entityManager->getRepository(Client::class)->find($clientId);

      
        $debtsQuery = $entityManager->getRepository(Dette::class)->createQueryBuilder('d')
            ->where('d.client = :client')
            ->setParameter('client', $client)
            ->getQuery();

      
        $dettes = $this->paginator->paginate(
            $debtsQuery,
            $request->query->getInt('page', 1), 
            8 
        );

      
        $totalAmount = array_sum(array_map(fn($dette) => $dette->getMontant(), $dettes->getItems()));

        $currentPage = $dettes->getCurrentPageNumber();
        $totalPages = ceil($dettes->getTotalItemCount() / 8);

        return $this->render('dette/index.html.twig', [
            'client' => $client,
            'dettes' => $dettes,
            'totalAmount' => $totalAmount,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
        ]);
    }
}
