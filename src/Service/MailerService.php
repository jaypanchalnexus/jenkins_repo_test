<?php

namespace App\Service;

use App\Entity\User;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class MailerService
{
    private Environment $twig;
    private Pdf $pdf;
    private EntrypointLookupInterface $entrypointLookup;
    private MailerInterface $mailer;

    public function __construct(Environment $twig, Pdf $pdf, EntrypointLookupInterface $entrypointLookup, MailerInterface $mailer){

        $this->twig = $twig;
        $this->pdf = $pdf;
        $this->entrypointLookup = $entrypointLookup;
        $this->mailer = $mailer;
    }

    public function sendEmail(User $user,$isAttach = 1){
        $this->entrypointLookup->reset();
        $html = $this->twig->render('email/demo-table.html.twig');

        $pdf = $this->pdf->getOutputFromHtml($html);
        $email = (new TemplatedEmail())
            ->from("jay@example.com")
            ->to($user->getEmail())
            ->subject("Hello ".$user->getFirstName().", This is Test Mail.")
            ->htmlTemplate("email/welcome.html.twig");

        if($isAttach){
            $email->attach($pdf,'demo.pdf');
        }
        $response = $this->mailer->send($email);
        return $email;

        dd($response, 89);
        return new Response();
    }
}