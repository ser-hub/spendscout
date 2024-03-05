<?php

namespace App\Tests\Application\Controller;

use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class MyTagsControllerTest extends WebTestCase
{
    private $client;
    private $container;
    private $crawler;
    private $tagData;
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

        $this->crawler = $this->client->request('GET', '/mytags');
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);

        $this->tagData = [
            'name' => 'test tag'
        ];
    }

    public function testCreateTagSuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->client->jsonRequest('POST', '/mytags/tags', $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $tagRepository = $this->container->get(TagRepository::class);
        $addedTag = $tagRepository->findOneBy($this->tagData);

        $this->assertNotNull($addedTag);
    }

    public function testCreateTagUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $this->tagData['name'] = '';
        $this->client->jsonRequest('POST', '/mytags/tags', $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }

    public function testEditTagSuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);

        $this->client->jsonRequest('POST', '/mytags/tags', $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();
        
        $tagRepository = $this->container->get(TagRepository::class);
        $addedTag = $tagRepository->findOneBy($this->tagData);
        $id = $addedTag->getId();
        
        $this->tagData['name'] = 'test tag 2';
        $this->client->jsonRequest('PUT', '/mytags/tags/' . $id, $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $editedTag = $tagRepository->find($id);

        $this->assertNotNull($editedTag);
        $this->assertEquals($this->tagData['name'], $editedTag->getName());
    }

    public function testEditTagUnprocessable(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);

        $this->client->jsonRequest('POST', '/mytags/tags', $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();
        
        $tagRepository = $this->container->get(TagRepository::class);
        $addedTag = $tagRepository->findOneBy($this->tagData);
        $id = $addedTag->getId();

        $this->tagData['name'] = '';
        $this->client->jsonRequest('PUT', '/mytags/tags/' . $id, $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsUnprocessable();
    }

    public function testEditTagNotFound(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 999;
        $this->client->jsonRequest('PUT', '/mytags/tags/' . $id, $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteTagSuccess(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        
        $this->client->jsonRequest('POST', '/mytags/tags', $this->tagData, [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();
        
        $tagRepository = $this->container->get(TagRepository::class);
        $addedTag = $tagRepository->findOneBy($this->tagData);
        $id = $addedTag->getId();

        $this->client->jsonRequest('DELETE', '/mytags/tags/' . $id, [], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseIsSuccessful();

        $TagRepository = $this->container->get(TagRepository::class);
        $editedTag = $TagRepository->find($id);

        $this->assertNull($editedTag);
    }

    public function testDeleteTagNotFound(): void
    {
        $csrfToken = $this->crawler->filter("meta[name=csrf-token]")->attr('content');
        $this->assertNotEmpty($csrfToken);
        $id = 999;
        $this->client->jsonRequest('DELETE', '/mytags/tags/' . $id, [], [
            'HTTP_anti-csrf-token' => $csrfToken]);
        $this->assertResponseStatusCodeSame(404);
    }
}
