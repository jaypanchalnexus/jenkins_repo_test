<?php


namespace App\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'en'){

        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event){
        $request = $event->getRequest();
        if(!$request->hasPreviousSession()){
            return;
        }
        if($locale = $request->attributes->get('_locale')){
            $request->getSession()->set('_locale',$locale);
        } else {
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
        return [];

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]]
        ];
    }

}