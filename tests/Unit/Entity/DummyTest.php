<?php


namespace App\Tests\Unit\Entity;


use App\Entity\Question;
use PHPUnit\Framework\TestCase;


class DummyTest extends TestCase
{

    public function test(): void{
        self::assertEquals('42', 42);
    }

    public function testdemo(): void{
        self::assertSame(42,42);
    }

    public function testSomeData() : void {
        $question = new Question();
        $question->setName('Demo');
//        $question->setOwner(1);
        $question->setQuestion('What is your question?');
        $question->setSlug('what-is-your-question');

        self::assertSame('Demo', $question->getName());
        self::assertSame('What is your question?', $question->getQuestion());
    }
}