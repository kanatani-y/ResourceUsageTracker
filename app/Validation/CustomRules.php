<?php

namespace App\Validation;

class CustomRules
{
    /**
     * 予約の終了時間が開始時間より後であるかチェック
     */
    public function validateDateTimeOrder(string $str, string $fields, array $data): bool
    {
        // `fields` には `start_time` のフィールド名が渡される
        $startDateTime = strtotime($data[$fields] ?? '');
        $endDateTime = strtotime($str);

        return $endDateTime > $startDateTime;
    }
}
