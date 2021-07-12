<?php
require 'basic/commonType.php';

class Feed extends CommonType
{
    public function buildCatalogController(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
        ]);
    }
}