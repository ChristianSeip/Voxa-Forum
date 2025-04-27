<?php

namespace App\Enum;

enum StickyStatus: int
{
	case None = 0;
	case Important = 1;
	case Global = 2;

	public function label(): string
	{
		return match ($this) {
			self::None      => 'sticky.none',
			self::Important => 'sticky.important',
			self::Global    => 'sticky.global',
		};
	}
}