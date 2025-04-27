<?php

namespace App\Form\Admin;

use App\Entity\Forum;
use App\Registry\PermissionRegistry;
use App\Repository\ForumRepository;
use App\Repository\RoleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumFormType extends AbstractType
{
	public function __construct(
		private ForumRepository $forumRepository,
		private RoleRepository $roleRepository,
		private PermissionRegistry $permissionRegistry
	) {}

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$forum = $options['data'];

		$builder
			->add('name', TextType::class, [
				'label' => 'admin.forum.edit.name',
			])
			->add('description', TextareaType::class, [
				'label'    => 'admin.forum.edit.description',
				'required' => false,
			])
			->add('position', IntegerType::class, [
				'label' => 'admin.forum.edit.position',
				'required' => false,
			])
			->add('isHidden', CheckboxType::class, [
				'label' => 'admin.forum.edit.hide',
				'required' => false,
			])
			->add('parent', EntityType::class, [
				'class'        => Forum::class,
				'label'        => 'admin.forum.edit.parent',
				'required'     => false,
				'placeholder'  => '---',
				'choices'      => $this->forumRepository->findAllExcluding($forum?->getId()),
				'choice_label' => 'name',
			]);

		$permissionsData = [];
		$roles = $this->roleRepository->findAll();

		foreach ($roles as $role) {
			$roleId = (string) $role->getId();
			$formData = [];

			foreach ($this->permissionRegistry->getAll() as $key => $meta) {
				if (!($meta['forumScoped'] ?? false)) {
					continue;
				}
				$perm = $forum->findForumPermission($key, $role);
				$formData[$key] = $perm ? $perm->getValue() : null;
			}

			$formData['role'] = $role;
			$formData['forumId'] = $forum->getId();
			$permissionsData[$roleId] = $formData;
		}

		$builder->add('forumPermissions', CollectionType::class, [
			'label'         => false,
			'mapped'        => false,
			'entry_type'    => ForumPermissionFormType::class,
			'entry_options' => [],
			'allow_add'     => false,
			'allow_delete'  => false,
			'by_reference'  => true,
			'data'          => $permissionsData,
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Forum::class,
		]);
	}
}
