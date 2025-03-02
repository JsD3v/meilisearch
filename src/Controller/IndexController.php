<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }

    #[Route('/article/{id}/{title}', name: 'app_article_show')]
    public function show(Article $article, string $title, int $id): Response
    {
        // Vérifier que l'ID et le titre dans l'URL correspondent à l'article récupéré
        if ($article->getId() !== $id || $article->getTitle() !== $title) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
