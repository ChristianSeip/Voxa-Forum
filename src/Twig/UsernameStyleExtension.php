<?php

namespace App\Twig;

use App\Entity\User;
use App\Entity\Forum;
use App\Service\UsernameStyleService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UsernameStyleExtension extends AbstractExtension
{
	public function __construct(private UsernameStyleService $styleService)
	{
	}

	/**
	 * Registers custom Twig functions.
	 *
	 * @return TwigFunction[] An array of available Twig functions.
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction('username_style', [$this, 'getStyleClass']),
		];
	}

	/**
	 * Returns the CSS class string for styling a username.
	 * Falls back to "user" if no user is provided.
	 *
	 * @param User|null  $user  The user entity (or null for guests).
	 * @param Forum|null $forum Optional forum context for moderator styling.
	 *
	 * @return string The CSS class name(s) to use for styling the username.
	 */
	public function getStyleClass(?User $user, ?Forum $forum = null): string
	{
		if (!$user) {
			return 'user';
		}
		return $this->styleService->getCssClass($user, $forum);
	}
}