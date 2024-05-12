<?php

namespace App\Spotify;

readonly class Profile
{
    public array $images;
    public array $explicit_content;
    public ?int $followers;
    public function __construct(
        public string $id,
        public string $display_name,
        public array $external_urls,
        array $followers,
        array $images,
        public string $uri,
        public ?string $product= null,
        array $explicit_content = [],
        public ?string $country = null,
        public ?string $email = null,
        ...$mixed
    ){
        $this->images = array_map(fn(array $image) => new Image(...$image), $images);
        $this->explicit_content = array_map(fn(array $filter) => new Filter(...$filter), $explicit_content);
        $this->followers = $followers['total'] ?? null;
    }
}
