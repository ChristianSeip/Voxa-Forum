<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class PreCodeDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'code';
	}

	public function usesOption(): bool
	{
		return false;
	}

	public function asHtml(ElementNode $el): string
	{
		$content = $this->getInnerHtml($el->getChildren());
		return sprintf('<pre>%s</pre>', $content);
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