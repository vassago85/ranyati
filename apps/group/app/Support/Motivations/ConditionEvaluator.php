<?php

namespace App\Support\Motivations;

/**
 * Evaluates the small condition language shared by questionnaire sections,
 * questions, document requirements and book blocks.
 *
 *   {"field": "answers.employment_category", "operator": "equals", "value": "Pensioner"}
 *   {"all": [ ...conditions ]}   {"any": [ ...conditions ]}
 *
 * `field` is a dot path into the context (profile.*, answers.*, firearm_count,
 * firearms.*.type ...). Wildcard paths resolve to a list, and equality
 * operators then match when any element matches.
 */
class ConditionEvaluator
{
    public const OPERATORS = [
        'equals' => 'equals',
        'not_equals' => 'does not equal',
        'in' => 'is one of (comma separated)',
        'not_in' => 'is not one of (comma separated)',
        'contains' => 'contains',
        'filled' => 'is filled in',
        'empty' => 'is empty',
        'gt' => 'is greater than',
        'gte' => 'is at least',
        'lt' => 'is less than',
        'lte' => 'is at most',
    ];

    /** Operators that ignore the comparison value. */
    public const UNARY = ['filled', 'empty'];

    /**
     * @param  array<string, mixed>|null  $condition
     * @param  array<string, mixed>  $context
     */
    public static function passes(?array $condition, array $context): bool
    {
        if (! $condition) {
            return true;
        }

        if (isset($condition['all']) && is_array($condition['all'])) {
            foreach ($condition['all'] as $child) {
                if (is_array($child) && ! self::passes($child, $context)) {
                    return false;
                }
            }

            return true;
        }

        if (isset($condition['any']) && is_array($condition['any'])) {
            $children = array_filter($condition['any'], 'is_array');
            if ($children === []) {
                return true;
            }
            foreach ($children as $child) {
                if (self::passes($child, $context)) {
                    return true;
                }
            }

            return false;
        }

        $field = trim((string) ($condition['field'] ?? ''));
        if ($field === '') {
            return true;
        }

        $operator = $condition['operator'] ?? 'equals';
        $actual = data_get($context, $field);
        $expected = $condition['value'] ?? null;

        return self::compare($actual, (string) $operator, $expected);
    }

    private static function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        $values = self::flatten($actual);

        return match ($operator) {
            'filled' => $values !== [],
            'empty' => $values === [],
            'equals' => in_array(self::norm($expected), $values, true),
            'not_equals' => ! in_array(self::norm($expected), $values, true),
            'in' => array_intersect($values, self::list($expected)) !== [],
            'not_in' => array_intersect($values, self::list($expected)) === [],
            'contains' => self::contains($values, self::norm($expected)),
            'gt', 'gte', 'lt', 'lte' => self::numeric($actual, $operator, $expected),
            default => false,
        };
    }

    /**
     * @return array<int, string> Normalised, non-empty scalar values.
     */
    private static function flatten(mixed $value): array
    {
        if (is_array($value)) {
            $out = [];
            array_walk_recursive($value, function ($v) use (&$out) {
                $n = self::norm($v);
                if ($n !== '') {
                    $out[] = $n;
                }
            });

            return $out;
        }

        $n = self::norm($value);

        return $n === '' ? [] : [$n];
    }

    private static function norm(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        return mb_strtolower(trim((string) $value));
    }

    /**
     * @return array<int, string>
     */
    private static function list(mixed $expected): array
    {
        $items = is_array($expected) ? $expected : explode(',', (string) $expected);

        return array_values(array_filter(array_map(fn ($v) => self::norm($v), $items), fn ($v) => $v !== ''));
    }

    /**
     * @param  array<int, string>  $values
     */
    private static function contains(array $values, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        foreach ($values as $value) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function numeric(mixed $actual, string $operator, mixed $expected): bool
    {
        $left = is_array($actual) ? count($actual) : $actual;
        if (! is_numeric($left) || ! is_numeric($expected)) {
            return false;
        }

        $left = (float) $left;
        $right = (float) $expected;

        return match ($operator) {
            'gt' => $left > $right,
            'gte' => $left >= $right,
            'lt' => $left < $right,
            'lte' => $left <= $right,
        };
    }

    /**
     * Clean a condition posted by the builder. Returns null for "no condition".
     *
     * @return array<string, mixed>|null
     */
    public static function normalize(mixed $condition): ?array
    {
        if (! is_array($condition)) {
            return null;
        }

        foreach (['all', 'any'] as $group) {
            if (isset($condition[$group]) && is_array($condition[$group])) {
                $children = array_values(array_filter(array_map([self::class, 'normalize'], $condition[$group])));

                return $children === [] ? null : [$group => $children];
            }
        }

        $field = trim((string) ($condition['field'] ?? ''));
        if ($field === '' || ! preg_match('/^[A-Za-z0-9_.*]+$/', $field)) {
            return null;
        }

        $operator = (string) ($condition['operator'] ?? 'equals');
        if (! array_key_exists($operator, self::OPERATORS)) {
            $operator = 'equals';
        }

        $out = ['field' => $field, 'operator' => $operator];
        if (! in_array($operator, self::UNARY, true)) {
            $out['value'] = mb_substr(trim((string) ($condition['value'] ?? '')), 0, 255);
        }

        return $out;
    }

    /**
     * Human-readable summary for admin screens.
     *
     * @param  array<string, mixed>|null  $condition
     */
    public static function describe(?array $condition): string
    {
        if (! $condition) {
            return 'Always';
        }

        foreach (['all' => ' AND ', 'any' => ' OR '] as $group => $glue) {
            if (isset($condition[$group])) {
                return implode($glue, array_map(fn ($c) => '('.self::describe($c).')', $condition[$group]));
            }
        }

        $op = self::OPERATORS[$condition['operator'] ?? 'equals'] ?? $condition['operator'];
        $value = in_array($condition['operator'] ?? '', self::UNARY, true) ? '' : ' "'.($condition['value'] ?? '').'"';

        return ($condition['field'] ?? '?').' '.$op.$value;
    }
}
