<?php

namespace App\EventSubscriber;

use Twig\Environment;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AjaxCsrfProtectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private CsrfTokenManagerInterface $csrfManager
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (preg_match('/login|register/', $event->getRequest()->getUri())) return;

        $requestMethod = $event->getRequest()->getMethod();
        $isStateChangingRequest = preg_match('/POST|PUT|PATCH|DELETE/', $requestMethod);

        if ($isStateChangingRequest) {
            $token = $event->getRequest()->headers->get('anti-csrf-token');
            if (!$this->csrfManager->isTokenValid(new CsrfToken('ajax_token', $token))) {
                throw new AccessDeniedHttpException('Invalid token.');
            }
        }
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $templateAttribute = $event->getAttributes(Template::class);
        $event->getRequest()->attributes->set('template', $templateAttribute);
    }

    public function onKernelView(ViewEvent $event): void
    {
        if (preg_match('/login|register/', $event->getRequest()->getUri())) return;
        
        $controllerResult = $event->getControllerResult();
        $controllerResult['token'] = 'ajax_token';

        $templatePath = $event->getRequest()->attributes->get('template')[0];
        $response = new Response($this->twig->render($templatePath->template, $controllerResult));

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
