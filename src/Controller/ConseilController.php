<?php
// src/Controller/ConseilController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConseilController extends AbstractController
{
    #[Route('/guide-achat', name: 'app_guide_achat')]
    public function guideAchat(): Response
    {
        return $this->render('conseil/guide_achat.html.twig');
    }

    #[Route('/comparateur', name: 'app_comparateur')]
    public function comparateur(): Response
    {
        return $this->render('conseil/comparateur.html.twig');
    }

    #[Route('/conseils-experts', name: 'app_conseils_experts')]
    public function conseilsExperts(): Response
    {
        return $this->render('conseil/conseils_experts.html.twig');
    }
}
