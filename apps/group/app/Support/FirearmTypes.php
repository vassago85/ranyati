<?php

namespace App\Support;

/**
 * Firearm type → allowed action mapping for the Storage module.
 *
 * Drives the two-level dependent dropdown on the intake form (pick a type,
 * then only that type's action list is valid) and the matching backend
 * validation rule. Adding a new action here makes it available immediately
 * on the intake form without any UI changes.
 */
class FirearmTypes
{
    /**
     * @return array<string, array{label: string, actions: array<int, string>}>
     */
    public static function all(): array
    {
        return [
            'rifle' => [
                'label' => 'Rifle',
                'actions' => [
                    'Bolt action',
                    'Self-loading',
                    'Single shot',
                    'Break action',
                    'Lever action',
                    'Pump action',
                    'Straight-pull',
                ],
            ],
            'shotgun' => [
                'label' => 'Shotgun',
                'actions' => [
                    'Self-loading',
                    'Double barrel over/under',
                    'Double barrel side-by-side',
                    'Pump action',
                    'Single barrel break action',
                    'Bolt action',
                    'Lever action',
                ],
            ],
            'handgun' => [
                'label' => 'Handgun',
                'actions' => [
                    'Semi-auto',
                    'Revolver',
                    'Single shot',
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function typeKeys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array<int, string>
     */
    public static function actionsFor(string $type): array
    {
        return self::all()[$type]['actions'] ?? [];
    }

    public static function isValid(string $type, string $action): bool
    {
        return in_array($action, self::actionsFor($type), true);
    }

    public static function typeLabel(string $type): string
    {
        return self::all()[$type]['label'] ?? ucfirst($type);
    }
}
