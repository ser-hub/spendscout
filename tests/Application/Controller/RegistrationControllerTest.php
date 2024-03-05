<?php

namespace App\Tests\Application\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterRedirectsOnSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Create your SpendScout account here');
        $this->assertSelectorCount(6, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Register', [
            'registration_form[firstName]' => 'Teste',
            'registration_form[lastName]' => 'Testev',
            'registration_form[email]' => 'testetestev@email.com',
            'registration_form[plainPassword]' => '123456tT*',
            'registration_form[terms]' => true,
            'registration_form[_token]' => $csrfToken->attr('value'),
        ]);

        $this->assertResponseRedirects('/login', 302);
    }

    public function testRegisterUnprocessableOnValidationFail(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Create your SpendScout account here');
        $this->assertSelectorCount(6, 'input');

        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Register', [
            'registration_form[firstName]' => 'Teste1',
            'registration_form[lastName]' => 'Testev',
            'registration_form[email]' => 'testetestev@email.com',
            'registration_form[plainPassword]' => '123456tT*',
            'registration_form[terms]' => true,
            'registration_form[_token]' => $csrfToken->attr('value'),
        ]);

        $this->assertResponseIsUnprocessable();
    }

    public function testRegisterShowsValidationErrorMessages(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Create your SpendScout account here');
        $this->assertSelectorCount(6, 'input');

        $crawler = $client->submitForm('Register', [
            'registration_form[firstName]' => 'Teste1',
            'registration_form[lastName]' => 'Testev1',
            'registration_form[email]' => 'test1testov@email.com',
            'registration_form[plainPassword]' => '123456T*',
            'registration_form[terms]' => false,
            'registration_form[_token]' => '',
        ]);

        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'First name does not meet the requirements');
        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'Last name does not meet the requirements');
        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'There is already an account with this email');
        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'The password does not meet the requirements');
        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'You must agree to our terms');
        $this->assertAnySelectorTextContains('.border-form-wrapper li', 'The CSRF token is invalid. Please try to resubmit the form.');
    }

    public function testRegisteredUserPasswordHashed(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h3', 'Create your SpendScout account here');
        $this->assertSelectorCount(6, 'input');

        $password = '123456tT*';
        $email = 'testetestev@email.com';
        $csrfToken = $crawler->filter('input[type=hidden]');
        $crawler = $client->submitForm('Register', [
            'registration_form[firstName]' => 'Teste',
            'registration_form[lastName]' => 'Testev',
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => $password,
            'registration_form[terms]' => true,
            'registration_form[_token]' => $csrfToken->attr('value'),
        ]);

        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $databaseUserPassword = $userRepository->findBy(['email' => $email])[0]->getPassword();

        $this->assertNotEquals($databaseUserPassword, $password);
    }
}
