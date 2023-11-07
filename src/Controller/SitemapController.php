<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Controller;

use DateTimeInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;
use WhiteDigital\SiteTree\Contracts\ContentTypeFinderInterface;
use WhiteDigital\SiteTree\Entity\MenuItem;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function array_key_exists;
use function array_merge;
use function in_array;
use function method_exists;
use function rtrim;
use function str_replace;

#[AsController]
class SitemapController extends AbstractController
{
    public function __construct(
        #[TaggedIterator(ContentTypeFinderInterface::class)]
        private $tagged,
    ) {}

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/sitemap.xml', defaults: ['_format' => 'xml'], methods: [Request::METHOD_GET])]
    public function sitemap(EntityManagerInterface $em): Response
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
        $i = 0;
        $now = new UTCDateTimeImmutable();
        foreach ($types as $type) {
            if (MenuItem::TYPE === $type->getType()) {
                continue;
            }

            try {
                $slug = $em->getRepository(SiteTree::class)->getSlug($type);
            } catch (DBALException) {
                continue;
            }
            if ($root === $slug) {
                $entries[$i++] = [
                    'loc' => str_replace($fakePath, '', $router->generate('sitemap_gen', referenceType: UrlGeneratorInterface::ABSOLUTE_URL)),
                    'lastmod' => $now->format(DateTimeInterface::ATOM),
                ];
            }
            $entries[$i++] = [
                'loc' => $path = str_replace($fakePath, '', $router->generate('sitemap_gen', ['path' => $slug], UrlGeneratorInterface::ABSOLUTE_URL)),
                'lastmod' => $now->format(DateTimeInterface::ATOM),
            ];
            $entities = [[]];
            foreach ($this->getParameter('whitedigital.site_tree.types') as $wdType) {
                $items = $em->getRepository($wdType['entity'])->findBy(['node' => $type]);
                if ([] !== $items) {
                    $entities[] = $items;
                }
            }
            $j = $i - 1;
            $last = $type->getUpdatedAt() ?? $type->getCreatedAt();
            $count = count($entities = array_merge(...$entities));
            if (0 === $count) {
                $last = $now;
            }
            foreach ($entities as $entity) {
                if (method_exists($entity, 'getIsActive') && !$entity->getIsActive()) {
                    continue;
                }
                $updated = ($entity->getUpdatedAt() ?? $entity->getCreatedAt());
                $last = $last > $updated ? $last : $updated;
                if (null !== $entity->getSlug()) {
                    $entries[$i++] = [
                        'loc' => rtrim($path, '/') . '/' . $entity->getSlug(),
                        'lastmod' => $updated->format(DateTimeInterface::ATOM),
                    ];
                }
            }
            if (array_key_exists($j, $entries)) {
                $entries[$j]['lastmod'] = ($last ?? $now)->format(DateTimeInterface::ATOM);
            }
            if ($root === $slug && array_key_exists($j - 1, $entries)) {
                $entries[$j - 1]['lastmod'] = ($last ?? $now)->format(DateTimeInterface::ATOM);
            }
        }

        $used = [];
        foreach ($this->tagged as $service) {
            if (in_array($service::class, $used, true)) {
                continue;
            }
            $used[] = $service::class;
            $entries = array_merge($entries, $service->getSitemapEntries());
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

    #[Route('/sitemap/0a6402de998c4ffe/{path}', 'sitemap_gen', requirements: ['path' => '.+'], defaults: ['path' => ''], methods: [Request::METHOD_GET])]
    public function gen(): Response
    {
        throw $this->createNotFoundException();
    }
}
