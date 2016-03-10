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
    function result_has_scores_method()
    {
        $this->assertEquals($this->scores, $this->result->scores());
    }

    /** @test */
    function result_has_category_method()
    {
        $this->assertEquals('neutral', $this->result->category());
    }
}
