<?php

use SentimentAnalysis\TokenValidator;

class TokenValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $tokenValidator;
    protected $ignoredWords;

    function setUp()
    {
        $this->ignoredWords = ['foo', 'bar'];

        $this->tokenValidator = new TokenValidator;
    }

    /** @test */
    function it_can_determine_if_token_should_be_calculated()
    {
        $this->assertFalse($this->tokenValidator->shouldBeCalculated('', $this->ignoredWords));
        $this->assertFalse($this->tokenValidator->shouldBeCalculated('bar', $this->ignoredWords));
        $this->assertTrue($this->tokenValidator->shouldBeCalculated('someword', $this->ignoredWords));
    }

    /** @test */
    function it_can_validate_token_length()
    {
        $this->assertFalse($this->tokenValidator->hasValidLength(''));
        $this->assertFalse($this->tokenValidator->hasValidLength('foofoofoofoofoofoo'));
        $this->assertTrue($this->tokenValidator->hasValidLength('foo'));
    }

    /** @test */
    function is_can_verify_ignored_words()
    {
        $this->assertTrue($this->tokenValidator->isOnIgnoredWords('foo', $this->ignoredWords));
        $this->assertFalse($this->tokenValidator->isOnIgnoredWords('qux', $this->ignoredWords));
    }
}
