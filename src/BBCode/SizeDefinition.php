<?php

namespace App\BBCode;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use JBBCode\TextNode;

class SizeDefinition extends CodeDefinition
{
	public function getTagName(): string
	{
		return 'size';
	}

	public function usesOption(): bool
	{
		return true;
	}

	public function asHtml(ElementNode $el): string
	{
		$content = $this->getInnerHtml($el->getChildren());
		$attr = $el->getAttribute();
		$size = is_array($attr) ? implode('', $attr) : (string)$attr;
		return sprintf('<span style="font-size:%spx">%s</span>', $size, $content);
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