<?php
namespace Planjeagenda\Component\Planjeagenda\Administrator\Enum;

final class AttendeeStatus
{
    public const PENDING = 0;
    public const CONFIRMED = 1;
    public const WAITING = 2;
    public const CANCELLED = 3;
    public const CHECKEDIN = 4;
    public const NOSHOW = 5;
}