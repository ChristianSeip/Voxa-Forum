<?php

namespace App\Form\Admin;

use App\Entity\Role;
use App\Registry\PermissionRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$role = $options['data'];
		$builder
			->add('name', TextType::class, [
				'label' => 'admin.role.name',
			])
			->add('description', TextType::class, [
				'label'    => 'admin.role.description',
				'required' => false,
			])
			->add('style', TextType::class, [
				'label'    => 'admin.role.style',
				'required' => false,
			]);
		foreach (PermissionRegistry::getAll() as $key => $meta) {
			$existing = $role->getPermissionByName($key);
			$value = $existing ? $existing->getValue() : $meta['default'];
			$builder->add('permission_' . $key, IntegerType::class, [
				'label'       => $meta['label'],
				'data'        => $value,
				'required'    => false,
				'mapped'      => false,
				'constraints' => [
					new Range(min: $meta['min'], max: $meta['max']),
				],
				'row_attr'    => [
					'data-category' => $meta['category'],
				],
			]);
		}
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Role::class,
		]);
	}
}
