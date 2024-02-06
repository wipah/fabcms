<?php

class SeoScoreCalculator {
    private $html;
    private $keywords;
    private $metaDescription;

    public function __construct($html, $keywords, $metaDescription = '') {
        $this->html = $html;
        $this->keywords = is_array($keywords) ? $keywords : [$keywords];
        $this->metaDescription = $metaDescription;
    }

    public function calculateScore() {
        $scores = [];

        if (empty($this->keywords) || (count($this->keywords) === 1 && empty($this->keywords[0]))) {
            return json_encode(['error' => 'No keywords provided for SEO score calculation.'], JSON_PRETTY_PRINT);
        }

        foreach ($this->keywords as $keyword) {

            $details = [];
            $potentialScore = 100; // Inizia con il punteggio massimo per la potenzialità

            // Calcolo lunghezza testo in parole e relative penalizzazioni o bonus
            $wordCount = $this->getWordCount();
            $details['textLength'] = $wordCount >= 800 ? 20 : 0;
            if ($wordCount < 800) {
                if ($wordCount < 100) {
                    $potentialScore -= 90;
                } else {
                    $missingWords = 800 - $wordCount;
                    $potentialScore -= ($missingWords / 700) * 80;
                }
            }

            // Calcolo densità keyword e penalizzazioni
            $keywordDensity = $this->getKeywordDensity($keyword);
            if ($keywordDensity > 2.5) {
                $details['keywordDensity'] = -30;
                $potentialScore -= 30; // Forte penalizzazione per densità delle keyword eccessiva
            } elseif ($keywordDensity >= 1 && $keywordDensity <= 2.5) {
                $details['keywordDensity'] = 30;
            } elseif ($keywordDensity > 0) {
                $details['keywordDensity'] = 15;
            } else {
                $details['keywordDensity'] = 0;
            }

            // Valutazione della meta description
            $metaDescriptionScore = $this->evaluateMetaDescription($keyword);
            $details['metaDescription'] = $metaDescriptionScore;
            if ($metaDescriptionScore < 0) {
                $potentialScore -= 10; // Penalizzazione per mancanza o problemi con la meta description
            }

            // Calcolo del punteggio totale e aggiunta dello score di potenzialità
            $totalScore = array_sum($details);
            $scores[$keyword] = [
                'totalScore' => max(0, min(100, $totalScore)),
                'details' => $details,
                'potentialScore' => max(0, $potentialScore) // Assicura che il punteggio sia almeno 0
            ];
        }

        // Restituisce i risultati in formato JSON
        return json_encode($scores, JSON_PRETTY_PRINT);
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
                    $penalty -= 5; // Penalizzazione per ogni intestazione seguita da meno di 100 parole
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

    private function evaluateMetaDescription($keyword) {
        $score = 0;
        $descriptionLength = strlen($this->metaDescription);

        if (empty($this->metaDescription)) {
            $score = -40; // Pesante penalizzazione se non c'è una meta description
        } elseif ($descriptionLength >= 100 && $descriptionLength <= 160) {
            $score = 10; // Lunghezza ottimale
            // Controlla se la keyword è presente nella meta description
            if (stripos($this->metaDescription, $keyword) !== false) {
                $score += 5; // Aumenta lo score se la keyword è presente
            }
        } elseif ($descriptionLength > 160) {
            $score = 5; // Presente ma troppo lunga
        }

        return $score;
    }

}
