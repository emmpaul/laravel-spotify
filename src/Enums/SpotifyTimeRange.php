<?php

namespace emmpaul\LaravelSpotify\Enums;

enum SpotifyTimeRange: string
{
    case LONG_TERM = 'long_term';
    case MEDIUM_TERM = 'medium_term';
    case SHORT_TERM = 'short_term';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
