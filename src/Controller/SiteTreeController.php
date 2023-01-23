<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use WhiteDigital\SiteTree\Entity\Redirect;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

use function filter_var;

use const FILTER_SANITIZE_URL;
use const FILTER_VALIDATE_URL;

#[AsController]
class SiteTreeController extends AbstractController
{
    #[Route('/{path}', name: 'wd_site_index', requirements: ['path' => '.+'], defaults: ['path' => '/'])]
    public function index(string $path, EntityManagerInterface $em, ParameterBagInterface $bag, RouterInterface $router): Response
    {
        $functions = new Functions($bag, $em->getRepository(SiteTree::class));
        $response = new Response(status: 214);

        $found = null;

        try {
            $found = $functions->findContentType($path);
        } catch (Exception) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        if (null !== $found && 'redirect' === $found->getType()) {
            $redirect = $em->getRepository(Redirect::class)->findOneBy(['node' => $found, 'isActive' => true]);
            if (null !== $redirect) {
                $url = $redirect->getContent();

                if (false === filter_var(value: filter_var(value: $url, filter: FILTER_SANITIZE_URL), filter: FILTER_VALIDATE_URL)) {
                    $url = $router->generate('wd_site_index', ['path' => $url], UrlGeneratorInterface::ABSOLUTE_URL);
                }

                return $this->redirect($url, $redirect->getCode());
            }

            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        return $this->render('index.html', response: $response);
    }
}
