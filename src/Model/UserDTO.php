<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public string $firstName,

        #[Assert\NotBlank]
        public string $lastName,

        #[Assert\NotBlank]
        public string $email,
    ) {
    }
}