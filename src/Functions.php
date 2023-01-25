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

        $found = null;
        foreach ($this->repository->findAllActiveByLevel(substr_count($slug, '/')) as $item) {
            if ($this->repository->getSlug($item) === $slug) {
                $found = $item;
            }
        }

        if (null === $found) {
            throw new NotFoundHttpException($this->translator->trans(Response::$statusTexts[Response::HTTP_NOT_FOUND], domain: 'SiteTree'));
        }

        if (null !== $end) {
            $types = $this->bag->get('whitedigital.site_tree.types');
            $item = $this->em->getRepository($types[$found->getType()]['entity'])->find($end);

            if (null !== $item) {
                return $item;
            }

            throw new NotFoundHttpException($this->translator->trans(Response::$statusTexts[Response::HTTP_NOT_FOUND], domain: 'SiteTree'));
        }

        return $found;
    }
}
