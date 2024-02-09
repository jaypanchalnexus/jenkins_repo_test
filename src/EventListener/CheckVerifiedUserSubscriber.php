<?php


namespace App\EventListener;


use App\Entity\User;
use App\Security\AccountNotVerifiedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{

    private RouterInterface $router;

    public function __construct(RouterInterface $router){

        $this->router = $router;
    }

    public function onCheckPassport(CheckPassportEvent $event){
        $passport = $event->getPassport();

        $user = $passport->getUser();
        if(!$user instanceof User){
            throw new \Exception('Unexpected user type.');
        }

        if(!$user->isIsVerified()){
            throw new AccountNotVerifiedException('User is not verified');
        }

    }

    public function onLoginFailure(LoginFailureEvent $event){
        if(!$event->getException() instanceof AccountNotVerifiedException){
            return;
        }
        $response = new RedirectResponse(
            $this->router->generate('reverify_email')
        );

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }

}