<?php

namespace App\Form\Admin;

use App\Entity\UserProfile;
use App\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('gender', ChoiceType::class, [
				'label' => 'profile.form.gender',
				'choices' => [
					'profile.gender.male' => Gender::Male,
					'profile.gender.female' => Gender::Female,
					'profile.gender.diverse' => Gender::Diverse,
					'profile.gender.none' => Gender::None,
				],
				'choice_label' => fn ($choice, $key) => $key,
				'required' => false,
				'placeholder' => 'profile.gender.none',
			])
			->add('birthdate', DateType::class, [
				'label' => 'profile.form.birth_date',
				'widget' => 'single_text',
				'required' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => UserProfile::class,
		]);
	}
}