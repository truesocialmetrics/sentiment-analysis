<?php

use SentimentAnalysis\Result;

class ResultTest extends PHPUnit_Framework_TestCase
{
    protected $scores;
    protected $result;

    function setUp()
    {
        $this->scores = [
            'positive' => 0.25,
            'negative' => 0.25,
            'neutral' => 0.5,
        ];

        $this->result = new Result($this->scores);
    }

    /** @test */
    function it_can_retrieve_scores()
    {
        $this->assertEquals($this->scores, $this->result->scores());
    }

    /** @test */
    function it_can_retrieve_category()
    {
        $this->assertEquals('neutral', $this->result->category());
    }
}
