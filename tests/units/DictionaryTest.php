<?php

use SentimentAnalysis\Dictionary;

class DictionaryTest extends PHPUnit_Framework_TestCase
{
    protected $dataDirectory;
    protected $dictionary;

    function setUp()
    {
        $this->dataDirectory = __DIR__ . '/data';
        $this->dictionary = new Dictionary($this->dataDirectory);
    }

    /** @test */
    function it_has_data_directory()
    {
        $this->assertEquals($this->dataDirectory, $this->dictionary->dataDirectory());
    }

    /** @test */
    function it_can_load_words_for_all_categories()
    {
        $this->assertInstanceOf(Dictionary::class, $this->dictionary->loadWordsForAllCategories($this->dataDirectory));

        $this->assertEquals(['positive'], $this->dictionary->positiveWords());
        $this->assertEquals(['negative'], $this->dictionary->negativeWords());
        $this->assertEquals(['neutral'], $this->dictionary->neutralWords());
        $this->assertEquals(['negation'], $this->dictionary->negationWords());
        $this->assertEquals(['ignored'], $this->dictionary->ignoredWords());
    }

    /** @test */
    function it_can_load_words_for_category()
    {
        $this->assertEquals(['positive'], $this->dictionary->loadWordsForCategory($this->dataDirectory, 'positive'));
    }

    /** @test */
    function it_can_retrieve_positive_words()
    {
        $positiveWords = $this->dictionary->positiveWords();

        $this->assertCount(1, $positiveWords);
        $this->assertEquals(['positive'], $positiveWords);
    }

    /** @test */
    function it_can_retrieve_negative_words()
    {
        $negativeWords = $this->dictionary->negativeWords();

        $this->assertCount(1, $negativeWords);
        $this->assertEquals(['negative'], $negativeWords);
    }

    /** @test */
    function it_can_retrieve_neutral_words()
    {
        $neutralWords = $this->dictionary->neutralWords();

        $this->assertCount(1, $neutralWords);
        $this->assertEquals(['neutral'], $neutralWords);
    }

    /** @test */
    function it_can_retrieve_negation_words()
    {
        $negationWords = $this->dictionary->negationWords();

        $this->assertCount(1, $negationWords);
        $this->assertEquals(['negation'], $negationWords);
    }

    /** @test */
    function it_can_retrieve_ignored_words()
    {
        $ignoredWords = $this->dictionary->ignoredWords();

        $this->assertCount(1, $ignoredWords);
        $this->assertEquals(['ignored'], $ignoredWords);
    }

    /** @test */
    function it_can_determine_if_word_on_category()
    {
        $this->assertFalse($this->dictionary->isWordFoundOnCategory('foo', 'positive'));
        $this->assertFalse($this->dictionary->isWordFoundOnCategory('foo', 'negative'));
        $this->assertFalse($this->dictionary->isWordFoundOnCategory('foo', 'neutral'));

        $this->assertTrue($this->dictionary->isWordFoundOnCategory('positive', 'positive'));
        $this->assertTrue($this->dictionary->isWordFoundOnCategory('negative', 'negative'));
        $this->assertTrue($this->dictionary->isWordFoundOnCategory('neutral', 'neutral'));
    }
}
