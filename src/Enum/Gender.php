<?php

namespace App\Enum;

enum Gender: string
{
	case Male = 'm';
	case Female = 'f';
	case Diverse = 'd';
	case None = 'n';

	public function label(): string
	{
		return match ($this) {
			self::Male    => 'gender.male',
			self::Female  => 'gender.female',
			self::Diverse => 'gender.diverse',
			self::None    => 'gender.none',
		};
	}
}