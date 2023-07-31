<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\SiteTree\Contracts\ContentTypeFinderInterface;
use WhiteDigital\SiteTree\Entity\AbstractNodeEntity;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

use function explode;
use function implode;
use function in_array;
use function ltrim;
use function rtrim;
use function substr_count;

final readonly class ContentTypeFinder implements ContentTypeFinderInterface
{
    private SiteTreeRepository $repository;

    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
    ) {
        $this->repository = $this->em->getRepository(SiteTree::class);
    }

    /**
     * @throws Exception
     */
    public function findContentType(string $path): SiteTree|AbstractNodeEntity
    {
        $slug = $orig = ltrim(rtrim($path, '/'), '/');
        $parts = explode('/', $slug);
        $end = $parts[$key = array_key_last($parts)];
        $count = substr_count($slug, '/');

        if ('' === $slug) {
            $slug = '/';
        }

        $found = null;
        foreach ($this->repository->findAllActiveByLevel($count) as $item) {
            if (in_array($this->repository->getSlug($item), [$slug, '/' . $slug], true)) {
                $found = $item;
            }
        }

        if (null === $found) {
            unset($parts[$key]);
            $slug = implode('/', $parts);
            $count--;

            foreach ($this->repository->findAllActiveByLevel($count) as $item) {
                if (in_array($this->repository->getSlug($item), [$slug, '/' . $slug], true)) {
                    $found = $item;
                }
            }

            if (null === $found) {
                throw new NotFoundHttpException($this->translator->trans('named_resource_not_found', ['%resource%' => '', '%id%' => $orig], domain: 'SiteTree'));
            }

            $types = $this->bag->get('whitedigital.site_tree.types');
            $entity = $types[$found->getType()]['entity'] ?? null;

            if (null !== $entity) {
                $item = $this->em->getRepository($entity)->findOneBySlug($end);

                if (null !== $item) {
                    return $item;
                }
            }

            throw new NotFoundHttpException($this->translator->trans(Response::$statusTexts[Response::HTTP_NOT_FOUND], domain: 'SiteTree'));
        }

        return $found;
    }

    public static function getDefaultPriority(): int
    {
        return 1;
    }
}
