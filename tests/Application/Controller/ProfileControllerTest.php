<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class ProfileControllerTest extends WebTestCase
{
    private $client;
    private $container;
    private $crawler;
    private $profileData;
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

        $this->crawler = $this->client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);

        $this->profileData = [
            'firstName' => 'Testa',
            'lastName' => 'Testova',
            'email' => 'testa1testova@email.com'
        ];
    }

    public function testEditProfileInfoSuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->client->jsonRequest('POST', '/profile/edit', $this->profileData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $security = $this->container->get(Security::class);
        $user = $security->getUser();

        $this->assertNotNull($user);
        $this->assertEquals($this->profileData['firstName'], $user->getFirstName());
        $this->assertEquals($this->profileData['lastName'], $user->getLastName());
        $this->assertEquals($this->profileData['email'], $user->getEmail());
    }

    public function testEditProfileInfoUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->profileData['email'] = '';
        $this->client->jsonRequest('POST', '/profile/edit', $this->profileData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }

    public function testEditProlfiePasswordSuccess(): void
    {
        $security = $this->container->get(Security::class);
        $oldPassword = $security->getUser()->getPassword();

        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->client->jsonRequest('PUT', '/profile/password', [
            'password' => '123456tTTT*'
        ], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $security = $this->container->get(Security::class);
        $this->assertNotNull($security->getUser());
        $this->assertNotEquals($security->getUser()->getPassword(), $oldPassword);
    }

    public function testEditProfilePasswordUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->client->jsonRequest('PUT', '/profile/password', [
            'password' => '123456'
        ], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }
}
