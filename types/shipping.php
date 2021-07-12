<?php
require 'basic/orderedType.php';

class Shipping extends OrderedType
{
    public function buildCatalogModel(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
            'fnName' => 'getQuote($address)'
        ]);
    }
}