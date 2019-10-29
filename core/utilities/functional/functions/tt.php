<?php
/**
 * Pass a string label to be prefixed with the output of the dump.
 *
 * @param string $label A label to identify the output items.
 *
 * @return Closure
 */
$tt = static function (string $label = ''): callable {
    /**
     * Pass any number of args to be printed and terminate with exit()
     *
     * @param mixed[] ...$items
     *
     * @return void
     */
    return function (...$items) use ($label): void {
        dump($label);
        $lastIndex = count($items) - 1;
        foreach ($items as $index => $item) {
            if ($index >= $lastIndex) {
                dd($item);
            }
            dump($item);
        }
    };
};

if ($declareGlobal ?? false && !function_exists('tt')) {
    $GLOBALS['tt'] = $tt;
    function tt(string $label = ''): callable
    {
        return $GLOBALS['tt']($label);
    }
}
