<?php

namespace App\Tests\Application\Controller;

use App\Repository\EntryRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class HomeControllerTest extends WebTestCase
{
    private $client;
    private $container;
    private $crawler;
    private $entryData;
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
        $this->client->followRedirects(false);

        $this->entryData = [
            'isExpense' => false,
            'name' => 'test expense',
            'tagId' => 1,
            'amount' => 14.99,
            'currencyId' => 1,
            'date' => '2023-01-22',
        ];
    }

    public function testCreateEntrySuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->client->jsonRequest('POST', '/entries', $this->entryData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $entryRepository = $this->container->get(EntryRepository::class);
        $addedEntry = $entryRepository->findOneBy(['name' => $this->entryData['name']]);

        $this->assertNotNull($addedEntry);
        $this->assertEquals($this->entryData['isExpense'], $addedEntry->isIsExpense());
        $this->assertEquals($this->entryData['amount'], $addedEntry->getAmount());
        $this->assertEquals($this->entryData['date'], $addedEntry->getDate()->format('Y-m-d'));
        $this->assertEquals($this->entryData['tagId'], $addedEntry->getTag()->getId());
        $this->assertEquals($this->entryData['currencyId'], $addedEntry->getCurrency()->getId());
    }

    public function testCreateEntryUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->entryData['amount'] = '11,11';
        $this->client->jsonRequest('POST', '/entries', $this->entryData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }

    public function testEditEntrySuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 1;
        $this->client->jsonRequest('PUT', '/entries/' . $id, $this->entryData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $entryRepository = $this->container->get(EntryRepository::class);
        $editedEntry = $entryRepository->find($id);

        $this->assertNotNull($editedEntry);
        $this->assertEquals($this->entryData['isExpense'], $editedEntry->isIsExpense());
        $this->assertEquals($this->entryData['amount'], $editedEntry->getAmount());
        $this->assertEquals($this->entryData['date'], $editedEntry->getDate()->format('Y-m-d'));
        $this->assertEquals($this->entryData['tagId'], $editedEntry->getTag()->getId());
        $this->assertEquals($this->entryData['currencyId'], $editedEntry->getCurrency()->getId());
    }

    public function testEditEntryUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->entryData['amount'] = '11,11';
        $id = 1;
        $this->client->jsonRequest('PUT', '/entries/' . $id, $this->entryData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }

    public function testEditEntryNotFound(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 999;
        $this->client->jsonRequest('PUT', '/entries/' . $id, $this->entryData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteEntrySuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 1;
        $this->client->jsonRequest('DELETE', '/entries/' . $id, [], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $entryRepository = $this->container->get(EntryRepository::class);
        $editedEntry = $entryRepository->find($id);

        $this->assertNull($editedEntry);
    }

    public function testDeleteEntryNotFound(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 999;
        $this->client->jsonRequest('DELETE', '/entries/' . $id, [], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseStatusCodeSame(404);
    }
}
