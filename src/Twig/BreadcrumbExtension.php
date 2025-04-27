<?php

namespace App\Twig;

use App\Entity\Forum;
use App\Entity\Topic;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class BreadcrumbExtension extends AbstractExtension implements GlobalsInterface
{
	private array $breadcrumbs = [];

	public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
	{
	}

	/**
	 * Builds the breadcrumb trail for a forum page.
	 * Includes the "Home" link and the parent forum (if any).
	 *
	 * @param Forum $forum The forum for which to build breadcrumbs.
	 */
	public function buildForForum(Forum $forum): void
	{
		$this->breadcrumbs = [
			['label' => 'Home', 'url' => '/'],
		];

		$parent = $forum->getParent();
		if ($parent) {
			$this->breadcrumbs[] = [
				'label' => $parent->getName(),
				'url'   => $this->urlGenerator->generate('app_forum_view', [
					'id'   => $parent->getId(),
					'slug' => $parent->getSlug()
				])
			];
		}
		$this->breadcrumbs[] = [
			'label' => $forum->getName(),
			'url'   => $this->urlGenerator->generate('app_forum_view', [
				'id'   => $forum->getId(),
				'slug' => $forum->getSlug()
			])
		];
	}

	/**
	 * Builds the breadcrumb trail for a topic page.
	 * Extends the forum breadcrumb with the topic title.
	 *
	 * @param Topic $topic The topic for which to build breadcrumbs.
	 */
	public function buildForTopic(Topic $topic): void
	{
		$this->buildForForum($topic->getForum());
		$this->breadcrumbs[] = [
			'label' => $topic->getTitle()
		];
	}

	/**
	 * Returns global variables to be available in all Twig templates.
	 *
	 * @return array Global Twig variables.
	 */
	public function getGlobals(): array
	{
		return [
			'breadcrumbs' => $this->breadcrumbs,
		];
	}
}
