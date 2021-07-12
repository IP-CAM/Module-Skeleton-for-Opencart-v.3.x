<?php
require 'basic/commonType.php';

class Fraud extends CommonType
{
    public function buildCatalogModel(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
            'fnName' => 'check($order_info)'
        ]);
    }
}