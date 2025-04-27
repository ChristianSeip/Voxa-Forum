<?php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;

class ForumModeratorFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('user', EntityType::class, [
			'class' => User::class,
			'label' => 'admin.forum.form.moderator',
			'choice_label' => 'username',
			'attr' => [
				'data-controller' => 'user-select',
			],
		]);
	}
}