<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class WelcomeControllerTest extends WebTestCase
{
    public function testWelcomeRendersTwigTemplate(): void
    {
        $client  = self::createClient();
        $crawler = $client->request('GET', '/welcome');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');

        self::assertSame('ja', $crawler->filter('html')->attr('lang'));
        self::assertCount(1, $crawler->filter('meta[name="viewport"]'));

        self::assertSelectorTextContains('title', 'Welcome');

        self::assertSelectorExists('h1[data-testid="welcome-heading"]');
        self::assertSelectorTextSame('h1[data-testid="welcome-heading"]', 'Welcome, Stockflow!');

        self::assertSelectorExists('[data-testid="stimulus-hello"][data-controller="hello"]');
        self::assertSelectorTextContains(
            '[data-testid="stimulus-hello"]',
            'If Stimulus runs, this sentence is replaced by hello_controller.js.',
        );
    }

    public function testWelcomeIncludesImportmapScript(): void
    {
        $client = self::createClient();
        $client->request('GET', '/welcome');

        self::assertSelectorExists('script[type="importmap"]');
    }

    public function testWelcomeHasCspHeaderWithNonce(): void
    {
        $client = self::createClient();
        $client->request('GET', '/welcome');

        $csp = $client->getResponse()->headers->get('Content-Security-Policy');

        self::assertNotNull($csp, 'CSP header should be present');
        self::assertStringContainsString("'strict-dynamic'", $csp);
        self::assertMatchesRegularExpression(
            "/script-src[^;]*'nonce-[A-Za-z0-9+\/=_-]+'/",
            $csp,
            'script-src should contain dynamic nonce',
        );
    }
}
