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
        dump($label);
        return array_map(function ($item) {
            dump($item);
            return $item;
        }, $items);
    };
};

if ($declareGlobal ?? false && !function_exists('trace')) {
    $GLOBALS['trace'] = $trace;
    function trace(string $label = '')
    {
        return $GLOBALS['trace']($label);
    }
}