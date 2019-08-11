<?php
/**
 * Hook to add the logpeek module to the frontpage.
 *
 * @param array &$links  The links on the frontpage, split into sections.
 * @return void
 */
function logpeek_hook_frontpage(&$links)
{
   assert('is_array($links)');
    assert('array_key_exists("links", $links)');

    $links['config'][] = [
        'href' => \SimpleSAML\Module::getModuleURL('logpeek/'),
        'text' => ['en' => 'SimpleSAMLphp logs access (Log peek)', 'no' => 'Vis simpleSAMLphp log'],
    ];
}
