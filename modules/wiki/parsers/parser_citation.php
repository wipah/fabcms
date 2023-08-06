<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/04/2018
 * Time: 09:47
 */

if (!$core->loaded)
    die ('Direct call detected');

$theRegex = '#\[cit\|(.*?)\]#miu';

$content = preg_replace_callback($theRegex,
    function ($theFirstMatch) {
        global $template;
        global $total;
        global $fabWikiCitationsIterator;
        global $fabWikiCitationsProgressive;
        global $core;

        $fabWikiCitationsProgressive++;

        switch ($core->shortCodeLang) {
            case 'it':
                $langBibliography = 'Bibliografia';
                break;
            case 'en':
            default:
                $langBibliography = 'Bibliography';
                break;
        }
        if (!isset($this->internalSpots['wikiInsideArticleBottom']['citations']))
            $this->internalSpots['wikiInsideArticleBottom']['citations'] .= '<h2>' . $langBibliography . '</h2>';

        if ($fabWikiCitationsIterator === 0 || !isset($fabWikiCitationsIterator)) {
            $this->internalSpots['wikiInsideArticleBottom']['citations'] .= '<div class="row"><!--adding the row-->' . PHP_EOL;
        }

        $fabWikiCitationsIterator++;

        if (substr($this->internalSpots['wikiInsideArticleBottom']['citations'], -strlen('</div>')) == '</div>') {
            $closedDiv = true;
            $this->internalSpots['wikiInsideArticleBottom']['citations'] = substr($this->internalSpots['wikiInsideArticleBottom']['citations'], 0, -6);
        }

        $elements = explode('||', $theFirstMatch[1]);
        foreach ($elements as $singleElement) {
            $fragments = explode('==', $singleElement);

            if ($fragments[0] === 'title')
                $title = $fragments[1];

            if ($fragments[0] === 'URI')
                $title = '<a target="_blank" href="' . $fragments[1] . '">' . $title . '</a>';

            if ($fragments[0] === 'authors')
                $authors = $fragments[1];

            if ($fragments[0] === 'website')
                $website = $fragments[1];

            if ($fragments[0] === 'journal')
                $journal = $fragments[1];
        }

        $this->internalSpots['wikiInsideArticleBottom']['citations'] .= '<div class="col-md-6">
                                                                            ' . $fabWikiCitationsProgressive . ') <strong><a name="cit-' . $fabWikiCitationsProgressive . '">
                                                                                ' . $title . '
                                                                            </a></strong> - <em>' . $authors . '</em>. ' . $journal . $website . '
                                                                         </div></div>';

        if ($fabWikiCitationsIterator === 2 && $closedDiv !== true) {
            $this->internalSpots['wikiInsideArticleBottom']['citations'] .= '</div><!--closing the row by 2->' . PHP_EOL;
            $fabWikiCitationsIterator = 0;
        }

        return '<sup>(<a href="#cit-' . $fabWikiCitationsProgressive . '">' . $fabWikiCitationsProgressive . '</a>)</sup>';

    }, $content);