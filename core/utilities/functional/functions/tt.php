<?php

/**
 * Pass a string label to be prefixed with the output of the dump.
 *
 * @param string $label A label to identify the output items.
 *
 * @return Closure
 */
$tt = function (string $label = ''): callable {
    /**
     * Pass any number of args to be printed and terminate with exit()
     *
     * @param mixed[] ...$items
     *
     * @return mixed[]
     */
    return function (...$items) use ($label): array {
        echo $label ? "\n{$label}\n" : '';
        foreach ($items as $item) {
            if (is_string($item)) {
                $count = strlen($item);
                echo "string({$count}) \"{$item}\"\n";
            } elseif (is_scalar($item)) {
                $type = gettype($item);
                echo "{$type} {$item}\n";
            } else {
                var_dump($item);
            }
        }
        exit();
    };
};

if ($declareGlobal ?? false && !function_exists('tt')) {
    $GLOBALS['tt'] = $tt;
    function tt(...$args)
    {
        return $GLOBALS['tt'](...$args);
    }
}
