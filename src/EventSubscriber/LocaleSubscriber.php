<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private Security            $security,
		private TranslatorInterface $translator,
		private readonly string     $defaultLocale = 'en'
	)
	{
	}

	/**
	 * Returns the events this subscriber wants to listen to.
	 *
	 * @return array The array of events and their associated methods.
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			RequestEvent::class => [['onKernelRequest', 1]],
		];
	}

	/**
	 * Sets the locale on the request and translator at the start of a request.
	 *
	 * @param RequestEvent $event The event triggered during the kernel request phase.
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();

		$user = $this->security->getUser();
		$locale = $user?->getLocale() ?? $request->getPreferredLanguage() ?? $this->defaultLocale;

		$request->setLocale($locale);
		$this->translator->setLocale($locale);
	}
}