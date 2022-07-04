<?php

namespace common\utils;

class WidgetUtil
{
    /**
     * @param string $mode
     * @return array
     */
    public static function ckeditorOptions(string $mode = 'full'): array
    {
        $result = [
            'options' => ['rows' => 3],
            'preset' => 'custom',
            'kcfinder' => true,
            'kcfOptions' => [
                'uploadURL' => '/upload/kcf',
                'uploadDir' => '@upload/kcf',
                'access' => [
                    'files' => [
                        'upload' => true,
                        'delete' => true,
                        'copy' => true,
                        'move' => true,
                        'rename' => true,
                    ],
                    'dirs' => [
                        'create' => true,
                        'delete' => true,
                        'rename' => true,
                    ],
                ],
                'thumbsDir' => '.thumbs',
                'thumbWidth' => 200,
                'thumbHeight' => 200,
            ],
            'clientOptions' => [],
        ];

        switch ($mode) {
            case 'basic':
                $result = array_merge($result, [
                    'clientOptions' => [
                        'allowedContent' => true,
                        'extraPlugins' => 'autoembed,embedsemantic,indentblock,basicstyles,justify,colorbutton,colordialog',
                        'toolbarGroups' => [
                            ['name' => 'mode', 'groups' => ['mode', 'tools']],
                            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup', 'colors']],
                            ['name' => 'paragraph', 'groups' => ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']],
                            ['name' => 'insert', 'groups' => ['insert', 'links']],
                        ],
                    ],
                ]);
                break;
            default:
                $result = array_merge($result, [
                    'clientOptions' => [
                        'allowedContent' => true,
                        'extraPlugins' => 'autoembed,embedsemantic,indentblock,basicstyles,justify,colorbutton,colordialog',
                        'toolbarGroups' => [
                            ['name' => 'mode', 'groups' => ['mode', 'tools']],
                            ['name' => 'clipboard', 'groups' => ['clipboard', 'undo']],
                            ['name' => 'editing', 'groups' => ['find', 'selection', 'spellchecker', 'editing']],
                            ['name' => 'styles', 'groups' => ['styles']],
                            '/',
                            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup', 'colors']],
                            ['name' => 'paragraph', 'groups' => ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']],
                            ['name' => 'insert', 'groups' => ['insert', 'links']],
                        ],
                    ],
                ]);
                break;
        }
        return $result;
    }
}