<?php


namespace App\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserAgentSubscriber implements EventSubscriberInterface
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {

        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $requestEvent){

//        $requestEvent->setResponse(new Response('Test response!'));

        $request = $requestEvent->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $this->logger->info(sprintf('User Agent is "%s"', $userAgent));
        return [];
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest',
            SecurityEvents::class => 'onKernelRequest'
        ];
    }

}