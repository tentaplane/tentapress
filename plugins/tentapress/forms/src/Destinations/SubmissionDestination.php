<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

interface SubmissionDestination
{
    public function key(): string;

    /**
     * @param  array<string,mixed>  $providerConfig
     * @param  array<string,mixed>  $fieldValues
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fieldDefinitions
     * @param  array<string,mixed>  $context
     */
    public function submit(array $providerConfig, array $fieldValues, array $fieldDefinitions, array $context = []): DestinationResult;
}
