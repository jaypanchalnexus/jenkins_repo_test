<?php


namespace App\Form;


use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerFormType extends AbstractType
{

    private QuestionRepository $questionRepository;

    public function __construct(QuestionRepository $questionRepository){

        $this->questionRepository = $questionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content')
            ->add('author', EntityType::class, [
                'class' => Question::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose Question',
                'choices' => $this->questionRepository->findAllTitleAlphabatically()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Answer::class
        ]);
    }


}