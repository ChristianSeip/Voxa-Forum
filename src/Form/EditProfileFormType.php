<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EditProfileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		/** @var User $user */
		$user = $options['data'];
		$builder
			->add('email', TextType::class, [
				'label'       => 'profile.form.email',
				'constraints' => [
					new Assert\NotBlank(),
					new Assert\Email(),
				]
			])
			->add('plainPassword', PasswordType::class, [
				'label'    => 'profile.form.new_password',
				'mapped'   => false,
				'required' => false,
				'attr'     => ['autocomplete' => 'new-password'],
			])
			->add('userProfile', EditUserProfileFormType::class, [
				'label' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
