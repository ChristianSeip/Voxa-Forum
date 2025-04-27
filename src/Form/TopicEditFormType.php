<?php

namespace App\Form;

use App\Entity\Topic;
use App\Enum\StickyStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopicEditFormType extends AbstractType
{
	public function __construct(private TranslatorInterface $translator)
	{
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('title', TextType::class, [
				'label' => 'topic.form.title',
			])
			->add('stickyStatus', ChoiceType::class, [
				'label'   => 'topic.form.sticky_status',
				'choices' => array_combine(
					array_map(fn (StickyStatus $s) => $this->translator->trans($s->label(), [], 'messages'), StickyStatus::cases()),
					array_map(fn (StickyStatus $s) => $s->value, StickyStatus::cases())
				),
			])
			->add('isClosed', CheckboxType::class, [
				'label'    => 'topic.form.is_closed',
				'required' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Topic::class,
		]);
	}
}