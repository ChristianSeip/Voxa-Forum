<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class QuoteCiteDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'quote';
	}

	public function usesOption(): bool
	{
		return true;
	}

	public function asHtml(ElementNode $el): string
	{
		$author = $el->getAttribute();
		$author = is_array($author) ? implode('', $author) : (string) $author;
		$content = $this->collectInnerHtml($el);
		$content = preg_replace('/^(<br\s*\/?>)+/i', '', $content);
		$content = preg_replace('/(<br\s*\/?>)+$/i', '', $content);
		return sprintf(
			'<blockquote><p><em>%s</em></p>%s</blockquote>',
			htmlspecialchars(trim($author)),
			$content
		);
	}

	private function collectInnerHtml(ElementNode $el): string
	{
		$html = '';
		foreach ($el->getChildren() as $child) {
			if ($child instanceof TextNode) {
				$text = $child->getAsText();
				$text = htmlspecialchars($text);
				$text = nl2br($text);
				$html .= $text;
			} elseif ($child instanceof ElementNode) {
				$definition = $child->getCodeDefinition();
				$html .= $definition->asHtml($child);
			}
		}
		return $html;
	}
}
