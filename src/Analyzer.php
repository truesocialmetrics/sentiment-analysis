<?php

namespace SentimentAnalysis;

use InvalidArgumentException;
use SentimentAnalysis\Contracts\AnalyzerInterface;
use SentimentAnalysis\Contracts\TokenizerInterface;

class Analyzer implements AnalyzerInterface
{
    public $classes = ['positive', 'negative', 'neutral'];

    public $dictionary = [];

    public $ignoreList = [];

    public $negationList = [];

    public $priorProbability = [
        'positive' => 0.333333333333,
        'negative' => 0.333333333333,
        'neutral' => 0.333333333334,
    ];

    public $minTokenLength = 1;

    public $maxTokenLength = 15;

    /**
     * Tokenizer insance.
     *
     * @var \SentimentAnalysis\Contracts\TokenizerInterface $tokenizer
     */
    protected $tokenizer;

    /**
     * Create a new instance of Analyzer class.
     *
     * @param \SentimentAnalysis\Contracts\TokenizerInterface|null $tokenizer
     */
    public function __construct($tokenizer = null)
    {
        if (is_null($tokenizer)) {
            $tokenizer = new Tokenizer;
        }

        if (! $tokenizer instanceof TokenizerInterface) {
            throw new InvalidArgumentException(sprintf(
                'The $tokenizer argument must implement %s.', TokenizerInterface::class
            ));
        }

        $this->tokenizer = $tokenizer;

        $this->setup();
    }

    public function setup()
    {
        $this->loadAllClassesDictionary();

        $this->ignoreList = $this->loadWordsForList('ignore');

        $this->negationList = $this->loadWordsForList('negation');
    }

    /**
     * Get tokenizer instance.
     *
     * @return \SentimentAnalysis\Contracts\TokenizerInterface
     */
    public function tokenizer()
    {
        return $this->tokenizer;
    }

    /**
     * Analyze document.
     *
     * @param  string $document
     * @return \SentimentAnalysis\Contracts\ResultInterface
     */
    public function analyze($document)
    {
        $scores = $this->scores($document);

        return new Result($scores);
    }

    public function scores($document)
    {
        $document = $this->removeSpaceAfterNegationWords($document);

        $tokens = $this->tokenizer()->tokenize($document);

        $scores = [];

        foreach ($this->classes as $class) {
            $scores[$class] = $this->tokensScore($tokens, $class);
        }

        return $this->normalizeScoreValues($scores);
    }

    public function tokensScore(array $tokens, $class)
    {
        $score = 1;

        foreach ($tokens as $token) {
            if (! $this->isValidToken($token)) {
                continue;
            }

            $count = $this->getDictionaryValue($token, $class);

            $score *= ($count + 1);
        }

        return $score * $this->priorProbability[$class];
    }

    public function normalizeScoreValues(array $scores)
    {
        $totalScore = array_sum($scores);

        foreach ($this->classes as $class) {
            $scores[$class] = round($scores[$class] / $totalScore, 3, 10);
        }

        return $scores;
    }

    public function isValidToken($token)
    {
        if (strlen($token) < $this->minTokenLength) {
            return false;
        }

        if (strlen($token) > $this->maxTokenLength) {
            return false;
        }

        return ! in_array($token, $this->ignoreList);
    }

    public function getDictionaryValue($token, $class)
    {
        if (! isset($this->dictionary[$token][$class])) {
            return 0;
        }

        return $this->dictionary[$token][$class];
    }

    public function removeSpaceAfterNegationWords($document)
    {
        foreach ($this->negationList as $negationWord) {
            if (strpos($document, $negationWord) !== false) {
                $document = str_replace("{$negationWord} ", $negationWord, $document);
            }
        }

        return $document;
    }

    public function loadAllClassesDictionary()
    {
        foreach ($this->classes as $class) {
            $this->loadDictionaryFor($class);
        }
    }

    public function loadDictionaryFor($class)
    {
        $words = $this->loadWordsFor($class);

        foreach ($words as $word) {
            $word = trim($word);

            if (! isset($this->dictionary[$word][$class])) {
                $this->dictionary[$word][$class] = 1;
            }
        }
    }

    public function loadWordsFor($class)
    {
        return require __DIR__ . "/data/{$class}.php";
    }

    public function loadWordsForList($list)
    {
        $words = $this->loadWordsFor($list);

        return array_map(function($word) {
            return stripcslashes(trim($word));
        }, $words);
    }
}
