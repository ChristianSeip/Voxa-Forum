<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class UrlOptionDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'url';
	}

	public function usesOption(): bool
	{
		return true;
	}

	public function asHtml(ElementNode $el): string
	{
		$content = $this->getInnerHtml($el->getChildren());
		$attr = $el->getAttribute();
		$href = is_array($attr) ? implode('', $attr) : (string)$attr;
		return sprintf('<a href="%s">%s</a>', $href, $content);
	}

	private function getInnerHtml(array $children): string
	{
		$html = '';
		foreach ($children as $child) {
			if ($child instanceof TextNode) {
				$html .= $child->getAsText();
			}
			else {
				if ($child instanceof ElementNode) {
					$definition = $child->getCodeDefinition();
					$html .= $definition->asHtml($child);
				}
			}
		}
		return $html;
	}
}