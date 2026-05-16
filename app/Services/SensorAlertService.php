<?php

namespace App\Services;

use App\Services\FonnteService;
use App\Models\PengaturanTambak;

class SensorAlertService
{
    public function sendAlert($sensorData)
    {
        $setting = PengaturanTambak::first();

        if (!$setting) return;

        $ruleService = new RuleEngineService();

        $result = $ruleService->evaluate($sensorData, $setting->rule_sensor);

        if ($result['status'] === 'normal') {
            return;
        }

        $message = $this->buildMessage($sensorData, $result);

        $wa = new FonnteService();

        foreach ($setting->nomor_wa as $nomor) {
            $wa->send($nomor, $message);
        }
    }

    private function buildMessage($sensor, $result)
    {
        return "🚨 ALERT TAMBAK\n\n"
            . "PH: {$sensor['ph']} ({$result['ph']})\n"
            . "Turbidity: {$sensor['turbidity']} ({$result['turbidity']})\n\n"
            . "Segera cek kondisi tambak!";
    }
}