<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function app_home(ArticleRepository $repository): Response
    {
        $articles = $repository->findBy(['deletedAt' => null]);
        return $this->render('default/acceuil.html.twig', [
            'articles' => $articles
        ]);
    }
    #[Route('/categorie', name: 'app_categorie')]//iphone
public function app_categorie(ArticleRepository $repository): Response
{
    $articles = $repository->contains('iphone');
    return $this->render('categorie/iphone.html.twig', [
        'articles' => $articles
    ]);
}

}
