<?php

namespace Nocio\Headstart\Widgets;

use App;
use Str;
use Lang;
use Input;
use Markdown;

class Documentation extends CmsComponentList
{

    protected function getDirectives()
    {
        $directives = [];
        $directive = null;
        foreach (file(__DIR__ . '/documentation/directives.md') as $line) {
            if (starts_with($line, '##')) {
                if (!is_null($directive)) {
                    $directives[] = (object) $directive;
                }
                $directive = [
                    'title' => trim(str_replace('@', '', substr($line, 2))),
                    'icon' => 'icon-dot-circle-o',
                    'content' => ''
                ];
            } else {
                $directive['content'] .= $line;
            }
        }
        return $directives;
    }

    protected function getData()
    {
        $searchTerm = Str::lower($this->getSearchTerm());
        $searchWords = [];
        if (strlen($searchTerm)) {
            $searchWords = explode(' ', $searchTerm);
        }

        $documentation = $this->getDirectives();

        $items = [];
        foreach ($documentation as $item) {
            if ($searchWords && !$this->itemMatchesSearch($searchWords, $item)) {
                continue;
            }

            // disabled, non-applicable directives
            if (in_array($item->title, ['can', 'auth'])) {
                continue;
            }

            $item->content = Markdown::parse($item->content);

            $items[] = $item;
        }

        uasort($items, function ($a, $b) {
            return strcmp($a->title, $b->title);
        });

        return $items;
    }

    protected function updateList()
    {
        return ['#'.$this->getId('documentation') => $this->makePartial('items', [
                'items' => $this->getData()])];
    }

    protected function itemContainsWord($word, $item)
    {
        if (Str::contains(Str::lower($item->title), $word)) {
            return true;
        }

//        if (Str::contains(Str::lower($item->content), $word) && strlen($item->content)) {
//            return true;
//        }

        return false;
    }
}
