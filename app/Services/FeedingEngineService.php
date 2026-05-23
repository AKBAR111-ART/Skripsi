<?php

namespace App\Services;

class FeedingEngineService
{
    public function calculate($sensor, $profile, $rule)
    {
        // =========================
        // VALIDASI DATA
        // =========================
        if (!$sensor || !$profile) {
            return [
                'feed_gram' => 0,
                'status' => 'no-data'
            ];
        }

        // =========================
        // KONVERSI BIOMASSA (KG → GRAM)
        // =========================
        $biomassa = $profile->biomassa_udang * 1000;

        // =========================
        // BASE FEED (3% dari biomassa)
        // =========================
        $baseFeed = $biomassa * 0.03;

        // =========================
        // FACTOR AIR
        // =========================
        $factor = 1.0;

        if (
            $sensor->ph_status === 'good' &&
            $sensor->turbidity_status === 'good'
        ) {
            $factor = 1.0;
        } elseif (
            $sensor->ph_status === 'warning' ||
            $sensor->turbidity_status === 'warning'
        ) {
            $factor = 0.8;
        } else {
            $factor = 0.6;
        }

        // =========================
        // FINAL FEED
        // =========================
        $finalFeed = $baseFeed * $factor;

        return [
            'feed_gram' => round($finalFeed, 2),
            'biomassa_gram' => $biomassa,
            'base_feed' => $baseFeed,
            'factor' => $factor,
            'status' => 'ok'
        ];
    }
}