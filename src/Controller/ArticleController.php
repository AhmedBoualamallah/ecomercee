<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }
    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $currentImage = $article->getImage();
        
         $form = $this->createForm(ArticleType::class, $article, ['image' => $currentImage]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdatedAt(new DateTime());
            $newImage = $form->get('image')->getData();
            if ($newImage) {
                $this->handleFile($article, $newImage, $slugger);
            } else {
                # Si pas de nouvelle image, on resset la image courante (actuelle)
                $article->setImage($currentImage);

            } // end if($newImage)
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
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
}
