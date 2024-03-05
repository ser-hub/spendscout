<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class LoginControllerTest extends WebTestCase
{
    public function testLoginRedirectsOnSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Log in', [
            '_username' => 'test1testov@email.com',
            '_password' => '123456tT*',
            '_csrf_token' => $csrfToken->attr('value'),
        ]);

        $this->assertResponseRedirects('/', 302);
    }

    public function testLoginSetsLoggedUserOnSuccess(): void
    {
        $client = static::createClient();
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

        $container = static::getContainer();
        $security = $container->get(Security::class);
        $user = $security->getUser();
        $this->assertNotNull($user);
        $this->assertEquals($username, $user->getUserIdentifier());
    }
    
    public function testLoginRedirectsBackOnInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Log in', [
            '_username' => 'test1testov@email.com',
            '_password' => '123456t*',
            '_csrf_token' => $csrfToken->attr('value'),
        ]);

        $this->assertResponseRedirects('/login', 302);
    }
    
    public function testRegisterShowsValidationErrorMessages(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Log in', [
            '_username' => 'test1testov@email.com',
            '_password' => '123456t*',
            '_csrf_token' => $csrfToken->attr('value'),
        ]);

        $this->assertSelectorExists('.login-error');
        $this->assertAnySelectorTextContains('.login-error', 'Invalid credentials');
    }
    
    public function testLoginRedirectsBackOnCsrfFail(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Log into your account');
        $this->assertSelectorCount(3, 'input');

        $crawler = $client->submitForm('Log in', [
            '_username' => 'test1testov@email.com',
            '_password' => '123456t*',
        ]);

        $this->assertResponseRedirects('/login', 302);
    }
}
