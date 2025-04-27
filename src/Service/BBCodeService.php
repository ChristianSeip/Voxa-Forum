<?php

namespace App\Service;

use App\BBCode\DefinitionRegistry;
use JBBCode\Parser;

class BBCodeService
{
	private Parser $parser;

	/**
	 * Initializes the BBCode parser with custom definitions.
	 */
	public function __construct()
	{
		$this->parser = new Parser();
		DefinitionRegistry::register($this->parser);
	}

	/**
	 * Converts BBCode to safe HTML output.
	 * Escapes input and replaces `[br]` with line breaks.
	 *
	 * @param string $bbcode The BBCode input string.
	 *
	 * @return string The safely parsed and formatted HTML output.
	 */
	public function convertToSafeHTML(string $bbcode): string
	{
		$bbcode = htmlspecialchars($bbcode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$bbcode = str_replace('[br]', '<br>', $bbcode);
		$this->parser->parse($bbcode);
		$html = trim($this->parser->getAsHtml());
		return $this->autoParagraph($html);
	}

	/**
	 * Converts BBCode to styled HTML for WYSIWYG display.
	 * Automatically wraps plain output in a <div> if needed.
	 *
	 * @param string $bbcode The BBCode input string.
	 *
	 * @return string The formatted HTML string.
	 */
	public function convertToHTML(string $bbcode): string
	{
		$bbcode = str_replace('[br]', "\n", $bbcode); // Workaround: JBB cannot parse self-closing tags
		$this->parser->parse($bbcode);
		$html = nl2br(trim($this->parser->getAsHtml()));
		if (!preg_match('/^\s*<.*?>.*<\/.*?>\s*$/s', $html)) {
			$html = "<div>{$html}</div>";
		}
		return $html;
	}

	/**
	 * Converts HTML input to BBCode syntax.
	 * Handles basic formatting tags and replaces elements like `<div>`, `<a>`, `<img>`, etc.
	 *
	 * @param string $html The HTML input string.
	 *
	 * @return string The converted BBCode string.
	 */
	public function convertToBBCode(string $html): string
	{
		$bbcode = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$bbcode = preg_replace('#<div[^>]*>#i', '', $bbcode);
		$bbcode = str_replace('</div>', "\n", $bbcode);
		$replacements = [
			'#<pre>(.*?)</pre>#si'                => '[code]$1[/code]',
			'#<blockquote><em>(.*?)</em>(.*?)</blockquote>#si' => '[quote=$1]$2[/quote]',
			'#<blockquote>(.*?)</blockquote>#si'               => '[quote]$1[/quote]',

			'#<a href="([^"]+)">([^<]+)</a>#si' => '[url=$1]$2[/url]',
			'#<img[^>]*src="([^"]+)"[^>]*>#si'  => '[img]$1[/img]',

			'#<h1>(.*?)</h1>#si'                                 => '[size=24]$1[/size]',
			'#<span style="color:\s*([^;"]+);?">(.*?)</span>#si' => '[color=$1]$2[/color]',

			'#<(strong|b)>(.*?)</(strong|b)>#si' => '[b]$2[/b]',
			'#<(em|i)>(.*?)</(em|i)>#si'         => '[i]$2[/i]',
			'#<u>(.*?)</u>#si'                   => '[u]$1[/u]',
			'#<(del|s)>(.*?)</(del|s)>#si'       => '[s]$2[/s]',

			'#<ul>(.*?)</ul>#si' => '[list]$1[/list]',
			'#<li>(.*?)</li>#si' => '[*]$1',

			'#\[\*\]\s*#si'    => '[*]',
			'#\s*\[/list\]#si' => '[/list]',
			'#<br\s*/?>#si'    => '[br]',
		];
		foreach ($replacements as $pattern => $replacement) {
			$bbcode = preg_replace($pattern, $replacement, $bbcode);
		}
		return trim(preg_replace('/\s+/', ' ', $bbcode));
	}

	private function autoParagraph(string $html): string
	{
		if (trim($html) === '') {
			return '';
		}
		$blockElements = [
			'address', 'article', 'aside', 'blockquote', 'canvas', 'dd', 'div', 'dl', 'dt',
			'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4',
			'h5', 'h6', 'header', 'hr', 'li', 'main', 'nav', 'noscript', 'ol', 'output',
			'p', 'pre', 'section', 'table', 'tfoot', 'ul', 'video'
		];
		$pattern = '#(</?(?:' . implode('|', $blockElements) . ')[^>]*>)#i';
		$parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$output = '';
		$buffer = '';
		foreach ($parts as $part) {
			if (preg_match('#^</?(?:' . implode('|', $blockElements) . ')[^>]*>$#i', $part)) {
				if (trim($buffer) !== '') {
					$output .= '<p>' . trim($buffer) . '</p>';
					$buffer = '';
				}
				$output .= $part;
			} else {
				$buffer .= $part;
			}
		}
		if (trim($buffer) !== '') {
			$output .= '<p>' . trim($buffer) . '</p>';
		}
		return $output;
	}


}