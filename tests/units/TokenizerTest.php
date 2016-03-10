<?php

use SentimentAnalysis\Tokenizer;

class TokenizerTest extends PHPUnit_Framework_TestCase
{
    protected $tokenizer;

    function setUp()
    {
        $this->tokenValidator = new Tokenizer;
    }

    /** @test */
    function it_can_tokenize_document()
    {
        $document = 'Foo BAR baz';

        $tokens = $this->tokenValidator->tokenize($document);

        $this->assertCount(3, $tokens);
        $this->assertEquals(['foo', 'bar', 'baz'], $tokens);
    }
}
