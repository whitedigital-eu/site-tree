<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function str_replace;

#[AsController]
class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', methods: [Request::METHOD_GET], defaults: ['_format' => 'xml'])]
    public function sitemap(EntityManagerInterface $em)
    {
        $entries = [];
        /** @var RouterInterface $router */
        $router = $this->container->get('router');
        $fakePath = $router->generate('sitemap_gen', referenceType: UrlGeneratorInterface::ABSOLUTE_PATH);
        $criteria = ['isActive' => true, 'isVisible' => true];
        if ($this->getParameter('whitedigital.site_tree.sitemap.include_invisible')) {
            unset($criteria['isVisible']);
        }
        $types = $em->getRepository(SiteTree::class)->findBy($criteria, ['level' => Criteria::ASC]);
        try {
            $root = ltrim($this->getParameter('whitedigital.site_tree.redirect_root_to_slug'), '/');
        } catch (Exception) {
            $root = null;
        }
        foreach ($types as $type) {
            $slug = $em->getRepository(SiteTree::class)->getSlug($type);
            if ($root === $slug) {
                $entries[] = [
                    'loc' => str_replace($fakePath, '', $router->generate('sitemap_gen', referenceType: UrlGeneratorInterface::ABSOLUTE_URL)),
                    'lastmod' => $type->getUpdatedAt()->format(UTCDateTimeImmutable::ATOM),
                ];
            }
            $entries[] = [
                'loc' => str_replace($fakePath, '', $router->generate('sitemap_gen', ['path' => $slug], UrlGeneratorInterface::ABSOLUTE_URL)),
                'lastmod' => $type->getUpdatedAt()->format(UTCDateTimeImmutable::ATOM),
            ];
        }

        $result = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<urlset ' . "\n\t" .
            'xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" ' . "\n\t" .
            'xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance" ' . "\n\t" .
            'xsi:schemaLocation="https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($entries as $entry) {
            $result .= "<url>\n\t";
            $result .= '<loc>' . $entry['loc'] . "</loc>\n\t";
            $result .= '<lastmod>' . $entry['lastmod'] . "</lastmod>\n";
            $result .= "</url>\n";
        }
        $result .= '</urlset>';

        return new Response($result);
    }

    #[Route('/sitemap/0a6402de998c4ffe/{path}', 'sitemap_gen', methods: [Request::METHOD_GET], defaults: ['path' => ''], requirements: ['path' => '.+'])]
    public function gen()
    {
        return new NotFoundHttpException();
    }
}
