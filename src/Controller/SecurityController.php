<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use mysql_xdevapi\Exception;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{

    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig',[
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_email' => $authenticationUtils->getLastUsername()
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(){
        throw new Exception('Demo Exception');
    }

    #[Route('/admin', name: 'admin')]
    public function admin(){
        dd("Admin");
    }

    #[Route('/admin/login', name: 'admin_login')]
    public function admin_login(){
        dd("Admin Login");
    }

    #[Route('/test_email/{id}', name: 'test_email')]
    public function test_email(User $user, MailerService $mailerService){

        $mailerService->sendEmail($user);

        return new Response(json_encode(['message' => 'Mail sent successfully.']));
    }

    #[Route('/change_language/{lang}', name: 'change_language')]
    public function change_language(Request $request, string $lang){
        $request->getSession()->set('_locale',$lang);

        return $this->redirectToRoute('question-list');
    }

    #[Route('/authenticate/2fa/enable', name: 'app_2fa_enable')]
    #[IsGranted("ROLE_USER")]
    public function enable2fa(TotpAuthenticatorInterface $totpAuthenticator, EntityManagerInterface $em){
        $user = $this->getUser();
        if(!$user->isTotpAuthenticationEnabled()){
            $user->setTotpSecret($totpAuthenticator->generateSecret());
            $em->flush();
        }
        return $this->render('security/enable2fa.html.twig');
    }

    #[Route('/authentication/2fa/qr-code', name: 'app_qr_code')]
    public function displayGoogleAuthenticatorQrCode(TotpAuthenticatorInterface $totpAuthenticator){
        $qrcodecontent = $totpAuthenticator->getQRContent($this->getUser());
        $result = Builder::create()
            ->data($qrcodecontent)
            ->build();
        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }

}
