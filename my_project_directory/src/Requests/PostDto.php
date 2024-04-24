<?php

namespace App\Requests;

class PostDto
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,    
    )
    {
        
    }
}