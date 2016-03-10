<?php

namespace SentimentAnalysis;

use InvalidArgumentException;
use SentimentAnalysis\Contracts\AnalyzerInterface;
use SentimentAnalysis\Contracts\TokenizerInterface;
use SentimentAnalysis\Contracts\DictionaryInterface;

class Analyzer implements AnalyzerInterface
{
    /**
     * Invalid dictionary exception message.
     */
    const ERROR_INVALID_DICTIONARY = 'The $dictionary argument must implement %s.';

    /**
     * Invalid tokenizer exception message.
     */
    const ERROR_INVALID_TOKENIZER = 'The $tokenizer argument must implement %s.';

    /**
     * Minimum token length to calculate.
     */
    const MIN_TOKEN_LENGTH = 1;

    /**
     * Maximum token length to calculate.
     */
    const MAX_TOKEN_LENGTH = 15;

    protected $categories = ['positive', 'negative', 'neutral'];

    protected $priorProbability = [
        'positive' => 0.333333333333,
        'negative' => 0.333333333333,
        'neutral' => 0.333333333334,
    ];

    /**
     * Dictionary instance.
     *
     * @var \SentimentAnalysis\Contracts\DictionaryInterface $dictionary
     */
    protected $dictionary;

    /**
     * Tokenizer instance.
     *
     * @var \SentimentAnalysis\Contracts\TokenizerInterface $tokenizer
     */
    protected $tokenizer;

    /**
     * Create a new instance of Analyzer class.
     *
     * @param \SentimentAnalysis\Contracts\TokenizerInterface|null $tokenizer
     */
    public function __construct($dictionary = null, $tokenizer = null)
    {
        if (is_null($dictionary)) {
            $dictionary = new Dictionary(__DIR__ . '/data');
        }

        if (! $dictionary instanceof DictionaryInterface) {
            throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_DICTIONARY, DictionaryInterface::class));
        }

        if (is_null($tokenizer)) {
            $tokenizer = new Tokenizer;
        }

        if (! $tokenizer instanceof TokenizerInterface) {
            throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_TOKENIZER, TokenizerInterface::class));
        }

        $this->dictionary = $dictionary;
        $this->tokenizer = $tokenizer;
    }

    /**
     * Get dictionary instance.
     *
     * @return \SentimentAnalysis\Contracts\DictionaryInterface
     */
    public function dictionary()
    {
        return $this->dictionary;
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
        $tokens = $this->cleanUpAndTokenizeDocument($document);

        $scores = [];

        foreach ($this->categories as $category) {
            $scores[$category] = $this->calculateTokensScore($tokens, $category);
        }

        $scores = $this->normalizeScoreValues($scores);

        return new Result($scores);
    }

    public function cleanUpAndTokenizeDocument($document)
    {
        $document = $this->removeWhiteSpaceAfterNegationWords($document);

        return $this->tokenizer()->tokenize($document);
    }

    public function removeWhiteSpaceAfterNegationWords($document)
    {
        foreach ($this->dictionary()->negationWords() as $negationWord) {
            if (strpos($document, $negationWord) !== false) {
                $document = str_replace("{$negationWord} ", $negationWord, $document);
            }
        }

        return $document;
    }

    public function calculateTokensScore(array $tokens, $category)
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

    public function shouldTokenBeCalculated($token)
    {
        if (strlen($token) < self::MIN_TOKEN_LENGTH) {
            return false;
        }

        if (strlen($token) > self::MAX_TOKEN_LENGTH) {
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

    public function normalizeScoreValues(array $scores)
    {
        $totalScore = array_sum($scores);

        foreach ($this->classes as $class) {
            $scores[$class] = round($scores[$class] / $totalScore, 3, 10);
        }

        return $scores;
    }
}
