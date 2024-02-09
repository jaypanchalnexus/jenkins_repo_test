<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\MailerService;
use Knp\Snappy\Pdf;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class MailerTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $this->assertCount(1, 4);
        self::bootKernel();
        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send');


        $pdf = self::getContainer()->get(Pdf::class);
        $twig = self::getContainer()->get(Environment::class);
        $entryPointLookup = self::getContainer()->get(EntrypointLookupInterface::class);
        $mailerService = new MailerService($twig, $pdf, $entryPointLookup, $symfonyMailer);

        $user = new User();
        $user->setEmail('demo@opmail.com');
        $user->setFirstName('John');

        $email = $mailerService->sendEmail($user);
        $this->assertCount(1, $email->getAttachments());
    }
}
