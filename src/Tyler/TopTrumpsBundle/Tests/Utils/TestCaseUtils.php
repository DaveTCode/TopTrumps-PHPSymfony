<?php

namespace Tyler\TopTrumpsBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TestCaseUtils
{
    /**
     * Verify that the response passed in has JSON format content and matches
     * whatever status code requested.
     *
     * @param WebTestCase $testCase - Used to assert.
     * @param Response $response - The response from the client.
     * @param int $statusCode - Defaults to 200. Set otherwise.
     */
    public static function assertJsonResponse(WebTestCase $testCase, Response $response, $statusCode = 200)
    {
        $testCase->assertEquals(
            $statusCode, $response->getStatusCode(),
            $response->getContent()
        );
        $testCase->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }
}
