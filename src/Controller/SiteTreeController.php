<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

#[AsController]
class SiteTreeController extends AbstractController
{
    #[Route('/{path}', name: 'wd_site_index', requirements: ['path' => '.+'], defaults: ['path' => '/'])]
    public function index(string $path, EntityManagerInterface $em, ParameterBagInterface $bag): Response
    {
        $functions = new Functions($bag, $em->getRepository(SiteTree::class));
        $response = new Response(status: 214);

        try {
            $functions->findContentType($path);
        } catch (Exception) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        return $this->render('index.html', response: $response);
    }
}
