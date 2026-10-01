<?php

use Core\Adaptors\Config;

/**
 * Convert terminal output to HTML format.
 *
 * Colours come from the active theme in config/declarative-style.php, rather than a fixed set of
 * hardcoded CSS colour names - the 8 ansi-colours entries cover every base ANSI code (30-37 and
 * 40-47) exactly once each, so a code always resolves cleanly. The config only has one hex value
 * per colour (no separate "bold" variant), so the ANSI intensity bit (0 vs 1, e.g. "1;32" vs
 * "0;32") is rendered as font-weight instead of a different colour.
 *
 * @param string $string
 * @param string $fromFormat
 * @return string
 */
$toHtml = static function (string $string, string $fromFormat = 'terminal'): string {
    $themeName = Config::get('declarative-style.default');
    $theme = Config::get("declarative-style.themes.$themeName");
    $hexColours = Config::get('declarative-style.hex-colours');
    $ansiColours = Config::get('declarative-style.ansi-colours');

    $codeToName = array_reduce(
        array_keys($ansiColours),
        static function (array $lookup, string $name) use ($ansiColours): array {
            $lookup[$ansiColours[$name]['fg']] = $name;
            $lookup[$ansiColours[$name]['bg']] = $name;
            return $lookup;
        },
        []
    );

    $resolveThemeColour = static function (string $dotPath) use ($hexColours): string {
        [$name, $variant] = explode('.', $dotPath);
        return $hexColours[$name][$variant];
    };

    // $code is either "N;NN" (foreground - N is the bold flag) or a bare "NN" (background, never bold).
    $colourLookup = static function (string $code, bool $isBackground) use ($codeToName, $hexColours): array {
        $isBold = false;
        $base = $code;
        if (preg_match('/^(\d);(\d{2})$/', $code, $matches)) {
            $isBold = $matches[1] === '1';
            $base = $matches[2];
        }
        $name = $codeToName[$base];
        return ['hex' => $hexColours[$name][$isBackground ? 'bg' : 'fg'], 'bold' => $isBold];
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
                    $colour = $colourLookup($colourCode, false);
                    $convertedLine .= "color:{$colour['hex']};";
                    if ($colour['bold']) {
                        $convertedLine .= 'font-weight:bold;';
                    }
                }
                $backgroundColourCode = preg_match(
                    $colourIndicators['backgroundColour'],
                    $colourCodes,
                    $matches
                ) ? $matches[0] : '';
                if ($backgroundColourCode) {
                    $backgroundColour = $colourLookup($backgroundColourCode, true);
                    $convertedLine .= "background-color:{$backgroundColour['hex']};";
                }
                return "$converted$convertedLine'>$line</span>";
            },
            "<div style='background-color:{$resolveThemeColour($theme['background'])}; " .
            "color:{$resolveThemeColour($theme['foreground'])}; padding: 10px'>"
        ) . '</div>';
};


if (($declareGlobal ?? false) && !function_exists('toHtml')) {
    $GLOBALS['toHtml'] = $toHtml;
    function toHtml(string $string, string $fromFormat = 'terminal')
    {
        return $GLOBALS['toHtml']($string, $fromFormat);
    }
}
