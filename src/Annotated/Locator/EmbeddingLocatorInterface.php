<?php

declare(strict_types=1);

namespace Arcano\Annotated\Locator;

interface EmbeddingLocatorInterface
{
    /**
     * @return Embedding[]
     */
    public function getEmbeddings(): array;
}
