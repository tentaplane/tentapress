<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Contracts;

use TentaPress\Marketing\Services\MarketingSettings;

interface MarketingProvider
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /**
     * @return array<int,array{key:string,label:string,help:string,placeholder:string,default:string,required:bool,max:int}>
     */
    public function fields(): array;

    public function isConfigured(MarketingSettings $settings): bool;

    /**
     * @return array{head?:string,body-open?:string,body-close?:string}
     */
    public function render(MarketingSettings $settings): array;
}
