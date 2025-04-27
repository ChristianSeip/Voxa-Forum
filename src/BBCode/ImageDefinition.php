<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class ImageDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'img';
	}

	public function usesOption(): bool
	{
		return false;
	}

	public function asHtml(ElementNode $el): string
	{
		$content = $this->getInnerHtml($el->getChildren());
		return sprintf('<img src="%s" alt="Image" />', $content);
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