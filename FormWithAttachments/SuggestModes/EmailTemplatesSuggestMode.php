<?php

namespace Statamic\Addons\FormWithAttachments\SuggestModes;

use Statamic\API\Folder;
use Statamic\API\Config;
use Statamic\API\URL;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class EmailTemplatesSuggestMode extends AbstractMode
{
    public function suggestions()
    {
        error_log('CALLED');
        $suggestions = [];

        $url = URL::assemble(
            Config::get('system.filesystems.themes.url'),
            Config::get('theming.theme')
        );
        $templates = Folder::getFilesRecursively($url . '/templates/email');

        foreach ($templates as $template) {
            $template_pathinfo = pathinfo($template);

            array_push($suggestions, ['value' => $template_pathinfo['filename'], 'text' => ucfirst($template_pathinfo['filename'])]);
        }

        return $suggestions;
    }
}
