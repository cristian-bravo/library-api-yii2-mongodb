<?php

declare(strict_types=1);

namespace app\commons\helpers;

final class ArrayHelper
{
    /**
     * @param array<int, string> $values
     * @return array<int, string>
     */
    public static function uniqueStrings(array $values): array
    {
        $indexed = [];

        foreach ($values as $value) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $indexed[$trimmed] = $trimmed;
        }

        return array_values($indexed);
    }
}
