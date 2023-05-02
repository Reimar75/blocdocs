<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'help' => 'Z.B.: 3h Bouldern, 1,5 Std. Ausgleichstraining, 1 Stunde Campus-Board etc.',
                'attr' => [
                    'autofocus' => true,
                    'autocomplete' => 'off',
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('color', ChoiceType::class, [
                'choices' => \array_combine($options['colors'], $options['colors']),
                'expanded' => true,
            ])
            ->add('time', ChoiceType::class, [
                'choices' => $this->getChoicesTime(),
                'placeholder' => 'Zeit',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'colors' => null,
        ]);
    }

    private function getChoicesTime(): array
    {
        $array = [];
        $hours = 0;

        while ($hours < 5) {
            $hours += $hours >=2 ? 0.5 : 0.25;
            $minutes = $hours * 60;
            $formattedHours = \number_format($hours, 2, ',', '');
            $array[$formattedHours . ' Std.'] = $minutes;            
        }

        return $array;
    }
}
