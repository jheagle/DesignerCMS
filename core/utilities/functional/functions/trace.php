<?php

/**
 * Pass a string label to be prefixed with the output of the dump.
 *
 * @param string $label A label to identify the output items.
 *
 * @return Closure
 */
$trace = static function (string $label = ''): callable {
    /**
     * Pass any number of args to be printed and return all of the items provided as an array
     *
     * @param mixed[] ...$items
     *
     * @return mixed[]
     */
    return function (...$items) use ($label): array {
        echo $label ? "\n{$label}\n" : '';
        return array_map(function ($item) {
            if (is_string($item)) {
                $count = strlen($item);
                echo "string({$count}) \"{$item}\"\n";
            } elseif (is_scalar($item)) {
                $type = gettype($item);
                echo "{$type} {$item}\n";
            } else {
                var_dump($item);
            }
            return $item;
        }, $items);
    };
};

if ($declareGlobal ?? false && !function_exists('trace')) {
    $GLOBALS['trace'] = $trace;
    function trace(...$args)
    {
        return $GLOBALS['trace'](...$args);
    }
}