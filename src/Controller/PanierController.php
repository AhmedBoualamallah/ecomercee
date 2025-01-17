<?php

namespace App\Controller;

use App\Entity\Article;
use DateTime;
use App\Entity\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/panier')]
class PanierController extends AbstractController
# Créer l'action qui va rendre la vue 'panier/show_panier.html.twig'
{
    #[Route('/voir-mon-panier', name: 'show_panier', methods:['GET'])]
    public function showPanier(SessionInterface $session): Response
    {
        # Cette instruction permet de récupérer le panier en session et à défaut créer le panier s'il n'existe pas
        $panier = $session->get('panier', []);
        
        $total = 0;

        # EXERCICE : Faire le code qui permet de calculer le total par produit ET le total du panier.
        foreach($panier as $item) {
    
$totalItem = $item['article']->getPrix() *  $item['quantity'];
$total += $totalItem;

        }

        return $this->render('panier/show_panier.html.twig', [
            'total' => $total
        ]);
     } // end function showPanier()

        #[Route('/ajouter-un-produit/{id}', name: 'add_item', methods:['GET'])]
        public function addItem(Article $article, SessionInterface $session): Response
        {
    
            # Si dans la session le panier n'existe pas, la méthode get() retournera le second paramètre : un array
            $panier = $session->get('panier', []);

            if(empty($panier[$article->getId()])) {
                $panier[$article->getId()]['quantity'] = 1;
                $panier[$article->getId()]['article'] = $article;
        } else {
            // post-incrémentation : quantity + 1
            // pré-incrémentation : 1 + quantity
            ++$panier[$article->getId()]['quantity'];
        }
        # Ici, nous devons set() le panier en session, en lui passant $panier[]
        $session->set('panier', $panier);


        

        $this->addFlash('success', "Le produit a bien été ajouté à votre panier !");
        return $this->redirectToRoute('app_home');

    } // end function addItem()

    #[Route('/vider-le-panier', name: 'delete_panier', methods: ['GET'])]
    public function deletePanier(SessionInterface $session): Response
    {
$session->remove('panier');

$this->addFlash('success', "Votre panier est à nouveau vide");
return $this->redirectToRoute('app_home');
    }

#Exercice : créer l'action qui permet de supprimer un seul item du panier :

#[Route('/retirer-du-panier/{id}', name: 'delete_item', methods: ['GET'])]
public function deleteItem(int $id, SessionInterface $session): Response
{
    $panier = $session->get('panier');

    // array_key_exists() est une fonction native de PHP, qui permet de vérifier si une key existe dans un array.
    if(array_key_exists($id, $panier)) {
        unset($panier[$id]); # unset() permet de supprimer une variable (ou une clé dans un array)
    }

    $session->set('panier', $panier);
return $this->redirectToRoute('show_panier');
} // end function deleteItem()

#[Route('/valider-mon-panier', name: 'validate_commande', methods: ['GET'])]
public function validateCommande(SessionInterface $session, EntityManagerInterface $entityManager): Response
{
    $panier = $session->get('panier', []);

    if(empty($panier)) {
        $this->addFlash('warning', 'Votre panier est vide.');
        return $this->redirectToRoute('show_panier');
    }

    $commande = new Command();

    $commande->setCreatedAt(new DateTime());
    $commande->setUpdatedAt(new DateTime());

    $total = 0;


    foreach($panier as $item) {
        $totalItem = $item['article']->getPrix() * $item['quantity'];
        $total += $totalItem;

        $commande->setQuantity($item['quantity']);
//            $article = $item['produit'];
    }

//        $commande->setarticle($article);


    $commande->setStatus('en préparation');
    $commande->setUser($this->getUser());
    $commande->setTotal($total);

    $entityManager->persist($commande);
    $entityManager->flush();

    $session->remove('panier');

    $this->addFlash('success', "Votre commande a été prise en compte et est en préparation. Vous pouvez la retrouver dans 'Mes Commandes.'");
    return $this->redirectToRoute('app_home');

}// end function validate()
    } // end class

