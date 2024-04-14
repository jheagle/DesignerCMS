<?php

/**
 * Convert terminal output to HTML format.
 *
 * @param string $string
 * @param string $fromFormat
 * @return string
 */
$toHtml = static function (string $string, string $fromFormat = 'terminal'): string {
    $colourLookup = fn($code) => match ($code) {
        '0;30', '40' => 'black',
        '0;31', '41' => 'red',
        '0;32', '42' => 'green',
        '0;33', '43' => 'brown',
        '0;34', '44' => 'blue',
        '0;35', '45' => 'magenta',
        '0;36', '46' => 'cyan',
        '0;37', '47' => 'lightgray',
        '1;30' => 'darkgray',
        '1;31' => 'lightred',
        '1;32' => 'lightgreen',
        '1;33' => 'yellow',
        '1;34' => 'lightblue',
        '1;35' => 'lightmagenta',
        '1;36' => 'lightcyan',
        '1;37' => 'white',
    };

    $colourIndicators = [
        'any' => '/\[[0-1][;0-9]*m/',
        'colour' => '/(\d;[0-9]{2})/',
        'backgroundColour' => '/([0-9]{2})/',
    ];
    return array_reduce(
            explode("\e", $string),
            static function (string $converted, string $line) use ($colourIndicators, $colourLookup): string {
                $line = nl2br(str_replace(' ', '&nbsp;', str_replace('[0m', '', $line)));
                $colourCodes = preg_match($colourIndicators['any'], $line, $matches) ? $matches[0] : '';
                $convertedLine = '<span';
                if (!$colourCodes) {
                    return "$converted$convertedLine>$line</span>";
                }
                $convertedLine .= " style='";
                $line = str_replace($colourCodes, '', $line);
                $colourCode = preg_match($colourIndicators['colour'], $colourCodes, $matches) ? $matches[0] : '';
                if ($colourCode) {
                    $colourCodes = str_replace($colourCode, '', $colourCodes);
                    $colour = $colourLookup($colourCode);
                    $convertedLine .= "color:$colour;";
                }
                $backgroundColourCode = preg_match(
                    $colourIndicators['backgroundColour'],
                    $colourCodes,
                    $matches
                ) ? $matches[0] : '';
                if ($backgroundColourCode) {
                    $backgroundColour = $colourLookup($backgroundColourCode);
                    $convertedLine .= "background-color:$backgroundColour;";
                }
                return "$converted$convertedLine'>$line</span>";
            },
            "<div style='background-color: black; padding: 10px'>"
        ) . '</div>';
};


if (($declareGlobal ?? false) && !function_exists('toHtml')) {
    $GLOBALS['toHtml'] = $toHtml;
    function toHtml(string $string, string $fromFormat = 'terminal')
    {
        return $GLOBALS['toHtml']($string, $fromFormat);
    }
}
