<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class LogoutTest extends WebTestCase
{
    public function testLogout(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $security = $container->get(Security::class);
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $username = 'test1testov@email.com';
        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Log in', [
            '_username' => $username,
            '_password' => '123456tT*',
            '_csrf_token' => $csrfToken->attr('value'),
        ]);

        $this->assertEquals($crawler->getUri(), 'http://localhost/');
        $security = $container->get(Security::class);
        $user = $security->getUser();
        $this->assertNotNull($user);
        $this->assertEquals($username, $user->getUserIdentifier());

        $crawler = $client->clickLink('Log out');
        $this->assertEquals($crawler->getUri(), 'http://localhost/login');
        $user = $security->getUser();
        $this->assertEquals($user, null);
    }
}
