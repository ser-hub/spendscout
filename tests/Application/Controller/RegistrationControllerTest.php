<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private $crawler;
    protected function setUp(): void 
    {
        $client = static::createClient();
        $this->crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Create your SpendScout account here');
        $this->assertSelectorCount(4, 'input');
    }
    public function testRegisterSuccess(): void
    {
        
    }

    protected function tearDown(): void
    {
        $this->crawler = null;
    }
}
