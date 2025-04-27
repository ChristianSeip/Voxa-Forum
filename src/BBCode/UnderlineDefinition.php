<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class UnderlineDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'u';
	}

	public function usesOption(): bool
	{
		return false;
	}

	public function asHtml(ElementNode $el): string
	{
		$content = $this->getInnerHtml($el->getChildren());
		return sprintf('<u>%s</u>', $content);
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