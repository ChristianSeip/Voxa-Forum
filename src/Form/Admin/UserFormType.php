<?php

namespace App\Form\Admin;

use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
	public function __construct(private RoleRepository $roleRepository) {}

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('username', TextType::class, [
				'label' => 'admin.user.edit.username',
			])
			->add('email', EmailType::class, [
				'label' => 'admin.user.edit.email',
			])
			->add('plainPassword', PasswordType::class, [
				'label' => 'password',
				'required' => false,
				'mapped' => false,
			])
			->add('isVerified', CheckboxType::class, [
				'label'    => 'admin.user.edit.active',
				'required' => false,
			])
			->add('roles', ChoiceType::class, [
				'label' => 'admin.user.edit.roles',
				'choices' => $this->getRoleChoices(),
				'multiple' => true,
				'expanded' => false,
				'required' => false,
				'mapped' => false, // <<< WICHTIG
				'data' => $this->getAssignedRoleIds($options['data']),
			])
			->add('timezone', ChoiceType::class, [
				'label' => 'profile.preferences.timezone',
				'choices' => $this->getTimezoneChoices(),
				'placeholder' => 'default',
				'required' => false,
			])
			->add('locale', ChoiceType::class, [
				'label' => 'profile.preferences.language',
				'choices' => $this->getLocaleChoices(),
				'placeholder' => 'default',
				'required' => false,
			])
			->add('userProfile', UserProfileFormType::class, [
				'label' => false
			])
			->add('settings', UserSettingsFormType::class, [
				'label' => false
			]);
	}

	private function getRoleChoices(): array
	{
		$roles = $this->roleRepository->findAll();
		$choices = [];
		foreach ($roles as $role) {
			$choices[$role->getName()] = $role->getId();
		}
		return $choices;
	}

	private function getAssignedRoleIds(mixed $user): array
	{
		if (!$user instanceof User) {
			return [];
		}
		$assignedRoleIds = [];
		foreach ($user->getRolesAsObjects() as $userRole) {
			$assignedRoleIds[] = $userRole->getId();
		}
		return $assignedRoleIds;
	}

	private function getLocaleChoices(): array
	{
		$translationPath = __DIR__ . '/../../translations';
		$choices = [];
		foreach (glob($translationPath . '/messages.*.yaml') as $file) {
			if (preg_match('/messages\.(.+)\.yaml$/', basename($file), $matches)) {
				$locale = $matches[1];
				$label = \Locale::getDisplayLanguage($locale, $locale);
				$choices[$label] = $locale;
			}
		}
		if (empty($choices)) {
			$choices = [
				'English' => 'en',
				'Deutsch' => 'de',
			];
		}
		return $choices;
	}

	private function getTimezoneChoices(): array
	{
		$timezones = \DateTimeZone::listIdentifiers();
		$choices = [];
		foreach ($timezones as $tz) {
			$choices[$tz] = $tz;
		}
		return $choices;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}