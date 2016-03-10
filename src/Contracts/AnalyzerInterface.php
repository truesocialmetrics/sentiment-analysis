<?php

namespace SentimentAnalysis\Contracts;

interface AnalyzerInterface
{
    /**
     * Get dictionary instance.
     *
     * @return \SentimentAnalysis\Contracts\DictionaryInterface
     */
    public function dictionary();

    /**
     * Get tokenizer instance.
     *
     * @return \SentimentAnalysis\Contracts\TokenizerInterface
     */
    public function tokenizer();

    /**
     * Get token validator instance.
     *
     * @return \SentimentAnalysis\Contracts\TokeniValidatorInterface
     */
    public function tokenValidator();

    /**
     * Analyze document.
     *
     * @param  string $document
     * @return \SentimentAnalysis\Contracts\ResultInterface
     */
    public function analyze($document);
}
