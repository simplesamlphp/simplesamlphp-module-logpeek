<?php

declare(strict_types=1);

use SimpleSAML\Locale\Translate;
use SimpleSAML\Module;
use SimpleSAML\XHTML\Template;

/**
 * Hook to add the logpeek module to the config page.
 *
 * @param \SimpleSAML\XHTML\Template &$template The template that we should alter in this hook.
 */
function logpeek_hook_configpage(Template &$template): void
{
    $template->data['links'][] = [
        'href' => Module::getModuleURL('logpeek/'),
        'text' => Translate::noop('SimpleSAMLphp logs access (Log peek)'),
    ];

    $template->getLocalization()->addModuleDomain('logpeek');
}
