<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Bangla numerals and date formatting.
 * A Bangla news site showing "15 September 2026" and "1,234 views"
 * in Latin digits looks half-finished to the reader.
 */
class Bangla
{
    protected const DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
                              '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    protected const MONTHS = [
        1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
        5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
        9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
    ];

    protected const DAYS = [
        0 => 'রোববার', 1 => 'সোমবার', 2 => 'মঙ্গলবার', 3 => 'বুধবার',
        4 => 'বৃহস্পতিবার', 5 => 'শুক্রবার', 6 => 'শনিবার',
    ];

    /** 2026 -> ২০২৬ */
    public static function digits($value): string
    {
        return strtr((string) $value, self::DIGITS);
    }

    /** 1234 -> ১,২৩৪ */
    public static function number($value): string
    {
        return self::digits(number_format((int) $value));
    }

    /** ১৫ সেপ্টেম্বর ২০২৬ */
    public static function date($date): string
    {
        if (blank($date)) {
            return '';
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return self::digits($date->format('j')) . ' '
             . self::MONTHS[(int) $date->format('n')] . ' '
             . self::digits($date->format('Y'));
    }

    /** ১৫ সেপ্টেম্বর ২০২৬, ০৪:০৮ পূর্বাহ্ণ */
    public static function dateTime($date): string
    {
        if (blank($date)) {
            return '';
        }

        $date   = $date instanceof Carbon ? $date : Carbon::parse($date);
        $period = $date->format('A') === 'AM' ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ';

        return self::date($date) . ', ' . self::digits($date->format('h:i')) . ' ' . $period;
    }

    /** রোববার, ১৫ সেপ্টেম্বর ২০২৬ — for the masthead */
    public static function fullDate($date = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        return self::DAYS[(int) $date->format('w')] . ', ' . self::date($date);
    }

    /** ৫ মিনিট আগে / ৩ ঘণ্টা আগে / ১৫ সেপ্টেম্বর ২০২৬ */
    public static function ago($date): string
    {
        if (blank($date)) {
            return '';
        }

        $date    = $date instanceof Carbon ? $date : Carbon::parse($date);
        $minutes = $date->diffInMinutes(now());

        if ($minutes < 1) {
            return 'এইমাত্র';
        }

        if ($minutes < 60) {
            return self::digits((int) $minutes) . ' মিনিট আগে';
        }

        if ($minutes < 1440) {
            return self::digits((int) ($minutes / 60)) . ' ঘণ্টা আগে';
        }

        if ($minutes < 10080) {
            return self::digits((int) ($minutes / 1440)) . ' দিন আগে';
        }

        return self::date($date);
    }
}
