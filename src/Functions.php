<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\SiteTree\Entity\AbstractNodeEntity;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

use function array_key_last;
use function implode;
use function in_array;
use function is_numeric;
use function ltrim;
use function rtrim;
use function substr_count;

final readonly class Functions
{
    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
        private null|SiteTreeRepository|EntityRepository $repository = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function findContentType(string $path): SiteTree|AbstractNodeEntity
    {
        /** @var SiteTreeRepository $repo */
        $slug = ltrim(rtrim($path, '/'), '/');
        $parts = explode('/', $slug);
        $end = $parts[$key = array_key_last($parts)];
        if (is_numeric($end)) {
            unset($parts[$key]);
            $slug = implode('/', $parts);
        } else {
            $end = null;
        }
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
            throw new NotFoundHttpException($this->translator->trans('named_resource_not_found', ['%resource%' => '', '%id%' => $slug], domain: 'SiteTree'));
        }

        if (null !== $end) {
            $types = $this->bag->get('whitedigital.site_tree.types');
            $entity = $types[$found->getType()]['entity'] ?? null;

            if (null !== $entity) {
                $item = $this->em->getRepository($entity)->find($end);

                if (null !== $item) {
                    return $item;
                }
            }

            throw new NotFoundHttpException($this->translator->trans(Response::$statusTexts[Response::HTTP_NOT_FOUND], domain: 'SiteTree'));
        }

        return $found;
    }
}
