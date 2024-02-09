<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\EventListener\CsrfProtectionListener;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo) {

        $this->userRepo = $userRepo;
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() == '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        return new Passport(
            new UserBadge($email, function ($userIdentifier){
                $user = $this->userRepo->findOneBy(['email' => $userIdentifier]);
                if(!$user){
                    throw new UserNotFoundException();
                }
                return $user;
            }),
            new PasswordCredentials($password),
//            new CustomCredentials(function($credentials, User $user){
//                return $credentials == '12345';
//            }, $password)
            [
             new CsrfTokenBadge(
                 'authenticate',
                 $request->request->get('_csrf_token')
             ),
            new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if($target = $this->getTargetPath($request->getSession(), $firewallName)){
            return new RedirectResponse(
                $target
            );
        }

        return new RedirectResponse(
            '/'
        );
    }

//    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
//    {
//        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
//        return new RedirectResponse(
//            'login'
//        );
//    }

//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        return new RedirectResponse(
//            'login'
//        );
//    }

    protected function getLoginUrl(Request $request): string
    {
        return 'login';
    }


}
