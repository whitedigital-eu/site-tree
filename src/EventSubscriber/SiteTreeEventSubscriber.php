<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
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
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use WhiteDigital\SiteTree\Contracts\ContentTypeFinderInterface;
use WhiteDigital\SiteTree\Entity\Redirect;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function array_merge;
use function filter_var;
use function implode;
use function in_array;
use function ltrim;
use function str_starts_with;
use function strtolower;

use const FILTER_SANITIZE_URL;
use const FILTER_VALIDATE_URL;

final readonly class SiteTreeEventSubscriber implements EventSubscriberInterface
{
    private const EXCLUDES = [
        '/api',
        'sitemap.xml',
    ];

    private const DEV_EXCLUDES = [
        '/_wdt',
        '/_error',
        '/_profiler',
    ];

    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $em,
        private UrlMatcherInterface $matcher,
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
        private ContentTypeFinderInterface $finder,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $request = $requestEvent->getRequest();

        $excludes = array_merge(self::EXCLUDES, $this->bag->get('whitedigital.site_tree.excluded_path_prefixes'));
        if (in_array($this->bag->get('kernel.environment'), ['dev', 'test', ], true)) {
            $excludes = array_merge($excludes, self::DEV_EXCLUDES, $this->bag->get('whitedigital.site_tree.excluded_path_prefixes_dev'));
        }

        foreach ($excludes as $exclude) {
            if (str_starts_with($request->getPathInfo(), $exclude)) {
                return;
            }
        }

        try {
            $this->onKernelRequestSymfony($request);
            if (null !== $request->attributes->get('_controller')) {
                return;
            }
        } catch (Exception $exception) {
            if ($exception instanceof MethodNotAllowedHttpException) {
                throw $exception;
            }

            if ($exception instanceof LogicException) {
                return;
            }
        }

        if (($allowed = $this->bag->get('whitedigital.site_tree.allowed_methods')) && !in_array(strtolower($request->getMethod()), $allowed, true)) {
            throw $this->getException($request, new MethodNotAllowedException($allowed));
        }

        $response = new Response();
        $found = null;
        $path = $requestEvent->getRequest()->getPathInfo();

        if (null !== ($slug = $this->bag->get('whitedigital.site_tree.redirect_root_to_slug')) && '/' === $path) {
            $url = 'https://' . $request->server->get('HTTP_HOST') . '/' . ltrim($slug, '/');
            $requestEvent->setResponse(new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY));

            return;
        }

        try {
            $found = $this->finder->findContentType($path);
        } catch (NotFoundHttpException) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        if ($found instanceof SiteTree && 'redirect' === $found->getType()) {
            $redirect = $this->em->getRepository(Redirect::class)->findOneBy(['node' => $found]);
            if (null !== $redirect) {
                if ($redirect->getIsExternal()) {
                    $url = $redirect->getContent();

                    if (false === filter_var(value: filter_var(value: $url, filter: FILTER_SANITIZE_URL), filter: FILTER_VALIDATE_URL)) {
                        $url = '/' . $url;
                    }
                } else {
                    $content = ltrim($redirect->getContent(), '/');
                    $url = 'https://' . $request->server->get('HTTP_HOST') . '/' . $content;
                }

                $requestEvent->setResponse(new RedirectResponse($url, $redirect->getCode()));

                return;
            }

            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        try {
            $view = $this->twig->render($this->bag->get('whitedigital.site_tree.index_template'));
            $response->setContent($view);
        } catch (Exception) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        $requestEvent->setResponse($response);
    }

    private function getException(Request $request, MethodNotAllowedException $exception): MethodNotAllowedHttpException|LogicException
    {
        if (in_array($request->getMethod(), $exception->getAllowedMethods(), true)) {
            throw new LogicException($exception->getMessage());
        }

        $message = $this->translator->trans('method_not_allowed', ['%method%' => $request->getMethod(), '%uri%' => $request->getUriForPath($request->getPathInfo()), '%allowed%' => implode(', ', $exception->getAllowedMethods())], domain: 'SiteTree');

        return new MethodNotAllowedHttpException($exception->getAllowedMethods(), $message, $exception);
    }

    private function onKernelRequestSymfony(Request $request): void
    {
        if ($request->attributes->has('_controller')) {
            return;
        }

        try {
            if ($this->matcher instanceof RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }

            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (MethodNotAllowedException $e) {
            throw $this->getException($request, $e);
        }
    }
}
