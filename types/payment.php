<?php
require 'basic/orderedType.php';

class Payment extends OrderedType
{
    public function buildCatalogModel(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
            'fnName' => 'getMethod($address, $total)'
        ]);
    }

    public function buildCatalogController(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
        ]);
    }
}