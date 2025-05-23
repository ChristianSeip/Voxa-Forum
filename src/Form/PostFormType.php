<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Post;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('content', HiddenType::class, [
				'label'       => false,
				'required'    => true,
				'constraints' => [
					new NotBlank(['message' => 'post.form.message_required']),
				],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Post::class,
		]);
	}
}