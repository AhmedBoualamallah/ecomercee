<?php
// src/Controller/AdminController.php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractControl;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: '_dashboard')]
    public function index(UserRepository $userRepository, ArticleRepository $articleRepository, ContactRepository $contactRepository): Response
    {
        $users = $userRepository->findAll();
        $articles = $articleRepository->findAll();
        $contacts = $contactRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'articles' => $articles,
            'contacts' => $contacts,
        ]);
    }

    // l'ajout d'un article

    #[Route('/new', name: '_app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,ArticleRepository $articleRepository, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());
            $entityManager->persist($article);
            $entityManager->flush();

            $image = $form->get('image')->getData();

            if ($image) {
                $this->handleFile($article, $image, $slugger);
            } // end if($image)

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    private function handleFile(Article $article, UploadedFile $image, SluggerInterface $slugger)
    {

        # 1 - Déconstruire le nom du fichier
        # A - Variabiliser l'extension du fichier : l'extension est DEDUITE a partir du MIME type du fichier.
        $extension = '.' . $image->guessExtension();

        # 2 - Assainir le nom du fichier (retirer accents et espaces)
        $safeFilename = $slugger->slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));

        # 3 - Rendre le nom du fichier unique
        $newFilename = $safeFilename . '_' . uniqid("", true) . $extension;

        # 4 - Déplacer le fichier (upload dans notre application Symfony)
        # On utilise un try/catch lorsqu'une méthode lance (throw) une Exception (erreur)
        try {
            # On a défini un paramètre dans config/service.yaml qui est le chemin absolu du dossier 'uploads'.
            # On récupère la valeur (paramètre) avec getParameter() et le nom du param défini dans le fichier service.yaml.
            $image->move($this->getParameter('uploads_dir'), $newFilename);
            # Si tout s'est bien passé (aucune Exception lancée) alors on doit set le nom de la image en BDD.
            $article->setImage($newFilename);
        } catch (FileException $exception) {
            $this->addFlash('warning', "Le fichier de la image ne s'est pas importé correctement. Veuillez réessayer." . $exception->getMessage());
        } // end catch()
    } // end handleFile()


    // Méthodes pour gérer les utilisateurs, articles, et contacts
}
