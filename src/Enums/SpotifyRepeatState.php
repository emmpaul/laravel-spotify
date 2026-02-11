<?php

namespace emmpaul\LaravelSpotify\Enums;

enum SpotifyRepeatState: string
{
    case OFF = 'off';
    case TRACK = 'track';
    case CONTEXT = 'context';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
