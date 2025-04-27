<?php

namespace App\Form;

use App\Entity\UserProfile;
use App\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUserProfileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label'    => 'profile.form.real_name',
				'required' => false,
			])
			->add('gender', ChoiceType::class, [
				'label'        => 'profile.form.gender',
				'required'     => false,
				'choices' => Gender::cases(),
				'choice_label' => fn (Gender $gender) => 'profile.gender.' . strtolower($gender->name),
			])
			->add('birthDate', DateType::class, [
				'label'    => 'profile.form.birth_date',
				'required' => false,
				'widget'   => 'single_text',
			])
			->add('location', TextType::class, [
				'label'    => 'profile.form.location',
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
