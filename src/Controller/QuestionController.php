<?php


namespace App\Controller;


use App\Entity\Question;
use App\Factory\QuestionFactory;
use App\Factory\UserFactory;
use App\Form\AnswerFormType;
use App\Form\QuestionFormType;
use App\Message\TestMessage;
use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use App\Service\SlugService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
class QuestionController extends BaseController
{
    #[Route('/{page<\d+>}', name: 'question-list')]
    public function demo(QuestionRepository $questionRepo, SlugService $slugService, LoggerInterface $logger, MessageBusInterface $messageBus, int $page = 1) {
        $logger->info('inside the controller!');
        $questions = $questionRepo->findAll();
        $questionsQuery = $questionRepo->createTitleAlphabaticallyQuery();

        $pagerFanta = new Pagerfanta(
            new QueryAdapter($questionsQuery)
        );
        $pagerFanta->setMaxPerPage(5);
        $pagerFanta->setCurrentPage($page);
//        dd($questionsQuery);
        $user = $this->getUser()->getEmail();
//        dd($user);

        $message = new TestMessage();
        $messageBus->dispatch($message);

        return $this->render('question/list.html.twig',[
            'pager' => $pagerFanta
        ]);
    }

    #[Route('/question/{id}', name: 'show-question')]
    public function test($id,QuestionRepository $questionRepo){
        $question = $questionRepo->find($id);
        $answers = $question->getAnswers();

        return $this->render('question/show.html.twig',[
            'question' => $question,
            'answers' => $answers
        ]);
    }

    #[Route('/new-question', name: 'new-question')]
    public function new_question(EntityManagerInterface $em, SlugService $slugService, Request $request){
//        dd($request->getLocale());
        $request->getSession()->set('_locale','fr_FR');
        $form = $this->createForm(QuestionFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $question = $form->getData();

            $tags = $question->getTags();
            foreach ($tags as $tag) {
                $em->persist($tag);
            }

            $question->setSlug($slugService->kebabcase($question->getQuestion()));
            $question->setOwner($this->getUser());


            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('question-list');
        }
        return $this->render('question/new.html.twig',[
            'questionForm' => $form->createView(),
        ]);
    }

    #[Route('/question/{id}/edit', name: 'edit-question')]
    public function edit(Question $question, EntityManagerInterface $em, Request $request, TagRepository $tagRepo){
        $this->denyAccessUnlessGranted('EDIT',$question);
//        if($question->getOwner() != $this->getUser() ){
//            throw $this->createAccessDeniedException('You are not the owner!');
//        }
//        $tags = $tagRepo->findBy(['question' => $question],[]);
        $form = $this->createForm(QuestionFormType::class,$question);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $questionForm = $form->getData();
            dd($question->getTags(), $questionForm);
            $tags = $questionForm->getTags();

            foreach ($tags as $tag) {
                $em->persist($tag);
                $tag->addQuestion($question);
            }

            $questionForm->setSlug(str_replace(" ","-",strtolower($questionForm->getQuestion())));

            $em->persist($questionForm);
            $em->flush();

            return $this->redirectToRoute('question-list');
        }
        return $this->render('question/new.html.twig',[
            'questionForm' => $form->createView(),
        ]);
    }

    #[Route('/add-answer/{question}',name: 'add-answer')]
    public function add_answer(Request $request, Question $question, EntityManagerInterface $em){

        $form = $this->createForm(AnswerFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){;
            $answer = $form->getData();
            $answer->setQuestion($question);
            $answer->setUsername("Demo");

            $em->persist($answer);
            $em->flush();

            return $this->redirectToRoute('show-question',['id' => $question->getId()]);
        }

        return $this->render('answer/new.html.twig',[
            'answerForm' => $form->createView()
        ]);
        dd($questionid);
    }

    #[Route('/make-fake-users',name: 'make-fake-users')]
    public function make_fake_users(UserFactory $userFactory){
//        UserFactory::createMany(2);
        QuestionFactory::createMany(2, function (){
            return [
                'owner' => UserFactory::random()
            ];
        });
    dd("dasdas");
    }
}