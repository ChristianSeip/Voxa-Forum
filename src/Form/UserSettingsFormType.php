<?php

namespace App\Form;

use App\Entity\UserSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingsFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('showEmail', CheckboxType::class, [
				'label'    => 'profile.preferences.show_email',
				'required' => false,
			])
			->add('showGender', CheckboxType::class, [
				'label'    => 'profile.preferences.show_gender',
				'required' => false,
			])
			->add('showBirthDate', CheckboxType::class, [
				'label'    => 'profile.preferences.show_birth_date',
				'required' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => UserSettings::class,
		]);
	}
}
