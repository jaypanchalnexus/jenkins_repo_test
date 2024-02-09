<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, FormLoginAuthenticator $formLoginAuthenticator, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_user',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // TODO: in real app, send this as an real email
            $this->addFlash('success', 'Confirm your email at: '.$signatureComponents->getSignedUrl());

            return $this->redirectToRoute('question-list');
            // do anything else you need here, like send an email
            return $userAuthenticator->authenticateUser(
                $user,
                $formLoginAuthenticator,
                $request
            );


        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify_user', name: 'app_verify_user')]
    public function verify(Request $request, UserRepository $userRepository, VerifyEmailHelperInterface $verifyEmailHelper, EntityManagerInterface $entityManager){
        $user = $userRepository->find($request->query->get('id'));
        if(!$user){
            throw new NotFoundException('User not found');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $exception) {
                $this->addFlash('error', $exception->getReason());
                return $this->redirectToRoute('app_register');
        }
        $this->addFlash('success', 'User Verified!');
        $user->setIsVerified(true);
        $entityManager->flush($user);
        return $this->redirectToRoute('question-list');
    }

    #[Route('reverify_email', name: 'reverify_email')]
    public function reverify_email(){
        return $this->render('registration/reverify_email.html.twig');
    }
}
