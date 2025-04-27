<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserProfile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilePreferencesFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('locale', ChoiceType::class, [
				'label'    => 'profile.preferences.language',
				'choices'  => $this->getAvailableLocales(),
				'required' => true,
			])
			->add('timezone', ChoiceType::class, [
				'label'    => 'profile.preferences.timezone',
				'choices'  => array_combine(
					\DateTimeZone::listIdentifiers(),
					\DateTimeZone::listIdentifiers()
				),
				'required' => true,
			])
			->add('settings', UserSettingsFormType::class, [
				'label' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}

	private function getAvailableLocales(): array
	{
		$dir = __DIR__ . '/../../translations';
		$files = glob($dir . '/messages.*.yaml');
		$locales = [];
		foreach ($files as $file) {
			if (preg_match('/messages\.([a-z]{2})\.ya?ml$/', $file, $matches)) {
				$locale = $matches[1];
				$locales[strtoupper($locale)] = $locale;
			}
		}
		return $locales;
	}
}
