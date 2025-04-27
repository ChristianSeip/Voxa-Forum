<?php

namespace App\Form\Admin;

use App\Entity\Role;
use App\Registry\PermissionRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ForumPermissionFormType extends AbstractType
{
	public function __construct(private PermissionRegistry $permissionRegistry)
	{
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$role = $options['role'] ?? null;
		foreach ($this->permissionRegistry->getAll() as $key => $meta) {
			if (!($meta['forumScoped'] ?? false)) {
				continue;
			}
			$constraints = [];
			if (isset($meta['min']) || isset($meta['max'])) {
				$constraints[] = new Range([
					'min' => $meta['min'] ?? null,
					'max' => $meta['max'] ?? null,
				]);
			}
			$builder->add($key, IntegerType::class, [
				'label'       => $meta['label'],
				'required'    => false,
				'attr'        => [
					'class'                => 'form-control forum-permission-input',
					'data-role-permission' => $key
				],
				'constraints' => $constraints,
			]);
		}
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => null,
			'role'       => null,
		]);
	}
}
