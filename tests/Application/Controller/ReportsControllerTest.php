<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class ReportsControllerTest extends WebTestCase
{
    private $client;
    private $container;
    private $crawler;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $this->crawler = $this->client->submitForm('Log in', [
            '_username' => 'test1testov@email.com',
            '_password' => '123456tT*',
            '_csrf_token' => $csrfToken->attr('value'),
        ]);

        $this->assertEquals($this->crawler->getUri(), 'http://localhost/');
        $this->container = static::getContainer();
        $security = $this->container->get(Security::class);
        $user = $security->getUser();
        $this->assertNotNull($user);

        $this->crawler = $this->client->request('GET', '/reports');
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
    }

    public function testGetReportSuccess(): void
    {
        $this->client->request('GET', '/reports/report?currencyId=1&tagId=1&dateFrom=2022-01-01&dateTo=2023-01-01', [], [], []);
        $this->assertResponseIsSuccessful();
    }

    public function testGetReportUnprocessable(): void
    {
        $this->client->request('GET', '/reports/report', [], [], []);
        $this->assertResponseIsUnprocessable();
    }
}
