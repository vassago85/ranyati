<?php

namespace App\Support;

/**
 * Compact firearm reference for the questionnaire's make / model / calibre /
 * action typeaheads. Models point at makes by index to keep the embedded
 * JSON small; categories use FirearmTypes' lower-cased labels.
 */
class FirearmReference
{
    /**
     * @return array{
     *     makes: array<int, string>,
     *     models: array<int, array{0: int, 1: string, 2: string|null}>,
     *     calibres: array<int, array{0: string, 1: string|null, 2: array<int, string>}>,
     *     actions: array<string, array<int, string>>
     * }
     */
    public static function payload(): array
    {
        $makes = FirearmMakes::all();
        $makeIndex = array_flip($makes);

        $models = [];
        foreach (FirearmModels::all() as [$make, $model, $category]) {
            if (isset($makeIndex[$make])) {
                $models[] = [$makeIndex[$make], $model, $category];
            }
        }

        $calibres = [];
        foreach (Cartridges::CARTRIDGES as $name => $category) {
            $calibres[] = [$name, $category, Cartridges::ALIASES[$name] ?? []];
        }

        $actions = [];
        foreach (FirearmTypes::all() as $type) {
            $actions[$type['label']] = $type['actions'];
        }

        return compact('makes', 'models', 'calibres', 'actions');
    }
}
