<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Topic;
use Symfony\Component\Validator\Constraints\NotBlank;

class TopicCreateFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('title', TextType::class, [
				'label'       => 'topic.form.title',
				'constraints' => [
					new NotBlank(['message' => 'topic.form.title_required']),
				],
			])
			->add('message', HiddenType::class, [
				'mapped'      => false,
				'required'    => true,
				'constraints' => [
					new NotBlank(['message' => 'topic.form.message_required']),
				],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Topic::class,
		]);
	}
}
