<?php

class SeoScoreCalculator {
    private $html;
    private $keywords;

    public function __construct($html, $keywords) {
        $this->html = $html;
        $this->keywords = is_array($keywords) ? $keywords : [$keywords];
    }

    public function calculateScore() {
        $scores = [];

        foreach ($this->keywords as $keyword) {
            $details = [];

            // Calcolo lunghezza testo in parole
            $wordCount = $this->getWordCount();
            $details['textLength'] = $wordCount >= 800 ? 20 : 0;

            // Calcolo densità keyword
            $keywordDensity = $this->getKeywordDensity($keyword);
            if ($keywordDensity > 2.5) {
                $details['keywordDensity'] = -30; // Forte penalizzazione per densità eccessiva
            } elseif ($keywordDensity >= 1 && $keywordDensity <= 2.5) {
                $details['keywordDensity'] = 30;
            } elseif ($keywordDensity > 0) {
                $details['keywordDensity'] = 15;
            } else {
                $details['keywordDensity'] = 0;
            }

            // Calcolo presenza immagini, attributi alt e keyword in alt
            $imagesScore = $this->getImagesScore($keyword);
            $details['images'] = $imagesScore > 0 ? $imagesScore : -20; // Forte penalizzazione se non ci sono immagini

            // Verifica presenza intestazioni H2 ogni 250 parole
            $details['h2Tags'] = $this->getH2Score($wordCount);

            // Verifica contenuto adeguato dopo intestazioni H2, H3, H4
            $details['headersContent'] = $this->checkHeadersContent();

            // Calcola il punteggio totale e lo normalizza su 100
            $totalScore = array_sum($details);
            $scores[$keyword] = [
                'totalScore' => max(0, min(100, $totalScore)), // Assicura che il punteggio sia tra 0 e 100
                'details' => $details
            ];
        }

        return $scores;
    }

    private function getWordCount() {
        $text = strip_tags($this->html);
        return str_word_count($text);
    }

    private function getKeywordDensity($keyword) {
        $text = strtolower(strip_tags($this->html));
        $keywordCount = substr_count($text, strtolower($keyword));
        $wordCount = str_word_count($text);

        return ($wordCount > 0) ? ($keywordCount / $wordCount) * 100 : 0;
    }

    private function getImagesScore($keyword) {
        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        $images = $doc->getElementsByTagName('img');
        $score = 0;
        foreach ($images as $img) {
            $alt = $img->getAttribute('alt');
            if (!empty($alt)) {
                $score += 5; // Ogni immagine con alt contribuisce al punteggio
                if (stripos($alt, $keyword) !== false) {
                    $score += 10; // Bonus se l'alt contiene la keyword
                }
            }
        }
        return $score;
    }

    private function getH2Score($wordCount) {
        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        $h2s = $doc->getElementsByTagName('h2');
        $expectedH2Count = floor($wordCount / 250);
        $h2Count = $h2s->length;

        return min(20, $h2Count * 10); // Punteggio massimo per H2 limitato a 20
    }

    private function checkHeadersContent() {
        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        $penalty = 0;

        foreach (['h2', 'h3', 'h4'] as $tag) {
            $headers = $doc->getElementsByTagName($tag);
            foreach ($headers as $header) {
                $followingText = $this->getFollowingTextContent($header);
                if (str_word_count($followingText) < 100) {
                    $penalty -= 5; // Lieve penalizzazione per ogni header seguito da meno di 100 parole
                }
            }
        }

        return $penalty;
    }

    private function getFollowingTextContent($node) {
        $textContent = '';
        while ($node = $node->nextSibling) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if (in_array(strtolower($node->nodeName), ['h2', 'h3', 'h4'])) {
                    break; // Interrompe se trova un'altra intestazione
                }
            }
            $textContent .= $node->textContent;
        }
        return $textContent;
    }
}