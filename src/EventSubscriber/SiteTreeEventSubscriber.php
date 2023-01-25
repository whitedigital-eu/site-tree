<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\EventSubscriber;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WhiteDigital\SiteTree\Entity\Redirect;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

use function filter_var;
use function implode;
use function str_starts_with;

use const FILTER_SANITIZE_URL;
use const FILTER_VALIDATE_URL;

final readonly class SiteTreeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $em,
        private UrlMatcherInterface $matcher,
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    /**
     * @throws DBALException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        if (str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        try {
            $this->onKernelRequestSymfony($request);

            return;
        } catch (Exception $exception) {
            if ($exception instanceof MethodNotAllowedHttpException) {
                throw $exception;
            }
        }

        $functions = new Functions($this->em, $this->bag, $this->em->getRepository(SiteTree::class));
        $response = new Response();

        $found = null;

        try {
            $found = $functions->findContentType($requestEvent->getRequest()->getPathInfo());
        } catch (NotFoundHttpException) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        if ($found instanceof SiteTree && 'redirect' === $found->getType()) {
            $redirect = $this->em->getRepository(Redirect::class)->findOneBy(['node' => $found, 'isActive' => true]);
            if (null !== $redirect) {
                $url = $redirect->getContent();

                if (false === filter_var(value: filter_var(value: $url, filter: FILTER_SANITIZE_URL), filter: FILTER_VALIDATE_URL)) {
                    $url = '/' . $url;
                }

                $requestEvent->setResponse(new RedirectResponse($url, $redirect->getCode()));

                return;
            }

            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        $view = $this->twig->render('index.html');
        $response->setContent($view);

        $requestEvent->setResponse($response);
    }

    private function onKernelRequestSymfony(Request $request): void
    {
        if ($request->attributes->has('_controller')) {
            return;
        }

        try {
            $parameters = $this->matcher->match($request->getPathInfo());

            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (MethodNotAllowedException $e) {
            $message = $this->translator->trans('method_not_allowed', ['method' => $request->getMethod(), 'uri' => $request->getUriForPath($request->getPathInfo()), 'allowed' => implode(', ', $e->getAllowedMethods())], domain: 'SiteTree');

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }
}
