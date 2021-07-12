<?php
require 'basic/commonType.php';

class Module extends CommonType
{
    protected function getTemplate(string $template)
    {
        if (file_exists("templates/{$template}.module.btp"))
            return file_get_contents("templates/{$template}.module.btp");

        return parent::getTemplate($template);
    }

    public function buildCatalogController(string $template, array $params): void
    {
        parent::build($template, [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
        ]);
    }
}