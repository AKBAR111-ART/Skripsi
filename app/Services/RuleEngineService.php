<?php

namespace App\Services;

class RuleEngineService
{
    public function evaluate($sensor, $rule)
    {
        $result = [
            'ph' => $this->checkPh($sensor['ph'], $rule['ph']),
            'turbidity' => $this->checkTurbidity($sensor['turbidity'], $rule['turbidity']),
        ];

        $result['status'] = $this->finalStatus($result);

        return $result;
    }

    private function checkPh($value, $rule)
    {
        if ($value >= $rule['baik'][0] && $value <= $rule['baik'][1]) {
            return 'normal';
        }

        if ($value >= $rule['warning'][0] && $value <= $rule['warning'][1]) {
            return 'warning';
        }

        return 'bahaya';
    }

    private function checkTurbidity($value, $rule)
    {
        if ($value <= $rule['baik'][1]) {
            return 'normal';
        }

        if ($value <= $rule['warning'][1]) {
            return 'warning';
        }

        return 'bahaya';
    }

    private function finalStatus($result)
    {
        if ($result['ph'] === 'bahaya' || $result['turbidity'] === 'bahaya') {
            return 'bahaya';
        }

        if ($result['ph'] === 'warning' || $result['turbidity'] === 'warning') {
            return 'warning';
        }

        return 'normal';
    }
}