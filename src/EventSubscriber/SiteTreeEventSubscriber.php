<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use WhiteDigital\SiteTree\Entity\Redirect;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;
use function dump;
use function filter_var;
use function get_class_methods;
use function implode;
use function sprintf;
use const FILTER_SANITIZE_URL;
use const FILTER_VALIDATE_URL;

final class SiteTreeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ParameterBagInterface $bag,
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $em,
        private readonly UrlMatcherInterface $matcher,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
//            KernelEvents::CONTROLLER => [
//                'onKernelController',
//                -100,
//            ],
            KernelEvents::REQUEST => ['onKernelController', 100],
//            KernelEvents::CONTROLLER => ['onKernelController', 100],
        ];
    }

    public function onKernelController(ControllerEvent|RequestEvent|ResponseEvent $controllerEvent): void {
//        dump($controllerEvent->getRequest());exit;
//        $controllerEvent->setController(controller: static fn () => new Response($view));

        $request = $controllerEvent->getRequest();
        if(!$controllerEvent->isMainRequest()){
            return;
        }

        $route = false;
        try {
            $this->onKernelRequest($request);
            $route = true;
        } catch (Exception $exception){

        }

        if(true === $route){
            return;
        }

        $functions = new Functions($this->bag, $this->em->getRepository(SiteTree::class));
        $response = new Response(status: 214);

        $found = null;

        try {
            $found = $functions->findContentType($controllerEvent->getRequest()->getPathInfo());
        } catch (NotFoundHttpException) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        if (null !== $found && 'redirect' === $found->getType()) {
            $redirect = $this->em->getRepository(Redirect::class)->findOneBy(['node' => $found, 'isActive' => true]);
            if (null !== $redirect) {
                $url = $redirect->getContent();

                if (false === filter_var(value: filter_var(value: $url, filter: FILTER_SANITIZE_URL), filter: FILTER_VALIDATE_URL)) {
                    $url = '/'.$url;
                }

                //return $this->redirect($url, $redirect->getCode());

                $controllerEvent->setResponse(new RedirectResponse($url, $redirect->getCode()));
                return;
            }

            $response = new Response(status: Response::HTTP_NOT_FOUND);
        }

        //return $this->render('index.html', response: $response);
        $view = $this->twig->render('index.html');
        $response->setContent($view);
//        $controllerEvent->setController(controller: static fn () => $response);
        $controllerEvent->setResponse($response);
    }

    private function onKernelRequest(Request $request)
    {
        //$this->setCurrentRequest($request);

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // add attributes based on the request (routing)
        try {
            // matching a request is more powerful than matching a URL path + context, so try that first
            $parameters = $this->matcher->match($request->getPathInfo());

            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getUriForPath($request->getPathInfo()));

            if ($referer = $request->headers->get('referer')) {
                $message .= sprintf(' (from "%s")', $referer);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getUriForPath($request->getPathInfo()), implode(', ', $e->getAllowedMethods()));

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }
}
