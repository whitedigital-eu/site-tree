<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        protected EntityManagerInterface $em,
    ) {
        $this->functions = new Functions($this->em, $bag, $translator, $this->em->getRepository(SiteTree::class));
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     * @throws Exception
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
