<?php

if (!function_exists('coalesce')) {
    /**
     * Get and return the first value that does not match the value to be replaced.
     *
     * @param callable|mixed|null $filter
     * @param mixed ...$values
     *
     * @return mixed
     */
    function coalesce(mixed $filter = null, ...$values): mixed
    {
        $runnableFilter = is_callable($filter)
            ? $filter
            : function ($value) use ($filter): bool {
                return $value !== $filter;
            };
        foreach ($values as $value) {
            if ($runnableFilter($value)) {
                return $value;
            }
        }
        return is_callable($filter) ? null : $filter;
    }
}

$coalesce = static function ($filter = null, ...$values) {
    return coalesce($filter, ...$values);
};

if (($declareGlobal ?? false) && ($GLOBALS['coalesce'] ?? false)) {
    $GLOBALS['coalesce'] = $coalesce;
}
