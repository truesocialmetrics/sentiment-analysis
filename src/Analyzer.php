<?php

namespace SentimentAnalysis;

use InvalidArgumentException;
use SentimentAnalysis\Contracts\AnalyzerInterface;
use SentimentAnalysis\Contracts\TokenizerInterface;
use SentimentAnalysis\Contracts\DictionaryInterface;
use SentimentAnalysis\Contracts\TokenValidatorInterface;

class Analyzer implements AnalyzerInterface
{
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
     * Token validator instance.
     *
     * @var \SentimentAnalysis\Contracts\TokenValidatorInterface
     */
    protected $tokenValidator;

    /**
     * Create a new instance of Analyzer class.
     *
     * @param \SentimentAnalysis\Contracts\DictionaryInterface $dictionary
     * @param \SentimentAnalysis\Contracts\TokenizerInterface $tokenizer
     * @param \SentimentAnalysis\Contracts\TokenValidatorInterface $tokenValidator
     */
    public function __construct(
        DictionaryInterface $dictionary,
        TokenizerInterface $tokenizer,
        TokenValidatorInterface $tokenValidator
    )
    {
        $this->dictionary = $dictionary;
        $this->tokenizer = $tokenizer;
        $this->tokenValidator = $tokenValidator;
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
     * Get token validator instance.
     *
     * @return \SentimentAnalysis\Contracts\TokenValidatorInterface
     */
    public function tokenValidator()
    {
        return $this->tokenValidator;
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
            if (! $this->shouldTokenBeCalculated($token)) {
                continue;
            }

            $count = $this->isTokenFoundOnCategory($token, $category) ? 1 : 0;

            $score *= ($count + 1);
        }

        return $score * $this->priorProbability[$category];
    }

    public function shouldTokenBeCalculated($token)
    {
        return $this->tokenValidator()->shouldBeCalculated(
            $token,
            $this->dictionary()->ignoredWords()
        );
    }

    public function isTokenFoundOnCategory($token, $category)
    {
        return $this->dictionary()->isWordFoundOnCategory($token, $category);
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
