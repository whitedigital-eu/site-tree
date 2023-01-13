<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

use function is_array;
use function ltrim;
use function rtrim;
use function str_replace;
use function substr_count;

final readonly class Functions
{
    public function __construct(
        private ?ParameterBagInterface $bag = null,
        private null|SiteTreeRepository|EntityRepository $repository = null,
    ) {
    }

    #[Pure]
    public function isAssociative(mixed $array): bool
    {
        if (!is_array(value: $array) || [] === $array) {
            return false;
        }

        return !array_is_list(array: $array);
    }

    /**
     * @throws Exception
     */
    public function getSlug(string $lang, ?SiteTree $item = null, string $slug = ''): ?string
    {
        if ($item) {
            if (0 !== $item->getLevel()) {
                return $this->getSlug($lang, $this->repository->getParentById($item->getId()), $item->getSlug()[$lang] ?? '') . ('' !== $slug ? '/' . $slug : '');
            }

            return ($item->getSlug()[$lang] ?? '') . ('' !== $slug ? '/' . $slug : '');
        }

        return $slug;
    }

    public function findContentType(string $path): SiteTree
    {
        $languages = $this->bag->get('whitedigital.site_tree.languages');
        /** @var SiteTreeRepository $repo */
        $slug = ltrim(rtrim($path, '/'), '/');
        $lang = 'all';
        foreach ($languages as $language) {
            if (str_starts_with($slug, $key = $language . '/')) {
                $lang = $language;
                $slug = str_replace($key, '', $slug);
                break;
            }
        }

        $found = null;
        foreach ($this->repository->findAllActiveByMaxLevel(substr_count($slug, '/')) as $item) {
            if ($this->getSlug($lang, $item) === $slug) {
                $found = $item;
            }
        }

        if (null === $found) {
            throw new NotFoundHttpException(Response::$statusTexts[Response::HTTP_NOT_FOUND]);
        }

        return $found;
    }
}
