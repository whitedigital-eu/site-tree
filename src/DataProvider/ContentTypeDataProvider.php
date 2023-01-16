<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\SiteTree\ApiResource\ContentTypeResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

class ContentTypeDataProvider implements ProviderInterface
{
    private readonly Functions $functions;

    public function __construct(
        ParameterBagInterface $bag,
        protected iterable $collectionExtensions = [],
        protected ?EntityManagerInterface $em = null,
    ) {
        $this->functions = new Functions($bag, $this->em->getRepository(SiteTree::class));
    }

    /**
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $found = $this->functions->findContentType($uriVariables['id']);
        } catch (NotFoundHttpException $exception) {
            throw new $exception();
        }

        $resource = new ContentTypeResource();
        $resource->nodeId = $found->getId();
        $resource->node = SiteTreeResource::create($found, $context);
        $resource->type = $found->getType();

        return $resource;
    }
}
