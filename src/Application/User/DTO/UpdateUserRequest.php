<?php

namespace App\Application\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public string $login,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public string $phone,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public string $pass,
    ) {
    }
}
