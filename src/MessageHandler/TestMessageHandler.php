<?php


namespace App\MessageHandler;


use App\Message\TestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class TestMessageHandler
{
    #[AsMessageHandler]
    public function __invoke(TestMessage $message)
    {
//        dd($message);
    }
}