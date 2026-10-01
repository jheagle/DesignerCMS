<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class ToHtmlTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('toHtml')]
class ToHtmlTest extends TestCase
{
    /**
     * Given a plain string with no ANSI codes at all,
     * When converting to HTML,
     * Then it is wrapped in an unstyled span, inside the themed container.
     */
    #[Test]
    final public function toHtmlLeavesPlainTextUnstyled(): void
    {
        $result = toHtml('plain text');

        $this->assertStringContainsString("<div style='background-color:#000; color:#fff; padding: 10px'>", $result);
        $this->assertStringContainsString('<span>plain&nbsp;text</span>', $result);
    }

    /**
     * Given a normal-intensity ANSI foreground code,
     * When converting to HTML,
     * Then the span gets the matching theme colour, with no bold weight.
     */
    #[Test]
    final public function toHtmlResolvesNormalForegroundColourFromTheme(): void
    {
        $result = toHtml("\e[0;32mgreen text\e[0m");

        $this->assertStringContainsString("<span style='color:#0f0;'>green&nbsp;text</span>", $result);
    }

    /**
     * Given a bold-intensity ANSI foreground code,
     * When converting to HTML,
     * Then the span gets the SAME theme colour as the normal variant, plus bold weight -
     * the config has one hex value per colour, so intensity becomes font-weight, not a different colour.
     */
    #[Test]
    final public function toHtmlResolvesBoldForegroundColourAsSameColourPlusBoldWeight(): void
    {
        $result = toHtml("\e[1;32mbold green text\e[0m");

        $this->assertStringContainsString(
            "<span style='color:#0f0;font-weight:bold;'>bold&nbsp;green&nbsp;text</span>",
            $result
        );
    }

    /**
     * Given a combined foreground+background ANSI code (the only shape toHtml's own detection
     * regex actually recognizes as colourised at all - a background-only code with no leading
     * intensity+foreground portion was never matched, before or after this change),
     * When converting to HTML,
     * Then the span gets both the matching foreground and background theme colours.
     */
    #[Test]
    final public function toHtmlResolvesBackgroundColourFromTheme(): void
    {
        $result = toHtml("\e[0;37;44mwhite on blue\e[0m");

        $this->assertStringContainsString(
            "<span style='color:#fff;background-color:#00f;'>white&nbsp;on&nbsp;blue</span>",
            $result
        );
    }
}
