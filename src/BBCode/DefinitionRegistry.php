<?php

namespace App\BBCode;

use JBBCode\Parser;

class DefinitionRegistry
{
	public static function register(Parser $parser): void
	{
		$definitions = [
			new BoldDefinition(),
			new ItalicDefinition(),
			new UnderlineDefinition(),
			new StrikeDefinition(),
			new PreCodeDefinition(),
			new ColorDefinition(),
			new SizeDefinition(),
			new ImageDefinition(),
			new UrlOptionDefinition(),
			new UrlDirectDefinition(),
			new QuoteCiteDefinition(),
			new QuoteDefinition(),
			new ListDefinition(),
			new ListItemDefinition(),
		];
		foreach ($definitions as $definition) {
			$parser->addCodeDefinition($definition);
		}
	}
}