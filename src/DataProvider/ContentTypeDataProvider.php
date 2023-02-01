<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\SiteTree\ApiResource\ContentTypeResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

final readonly class ContentTypeDataProvider implements ProviderInterface
{
    private Functions $functions;

    public function __construct(
        ParameterBagInterface $bag,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
    ) {
        $this->functions = new Functions($em, $bag, $translator, $em->getRepository(SiteTree::class));
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $found = $this->functions->findContentType($uriVariables['id']);

        $resource = new ContentTypeResource();
        $resource->nodeId = $found->getId();
        $resource->node = SiteTreeResource::create($found, $context);
        $resource->type = $found->getType();

        return $resource;
    }
}
