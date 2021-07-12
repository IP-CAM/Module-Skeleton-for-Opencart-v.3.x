<?php
function exception_handler($exception)
{
    echo $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler');


$builder = new Builder();

if (!empty($_GET['getarchive'])) $builder->run();
else echo '<a href="?getarchive=true">Make skeleton</a>';

/**
 * property type in [advertise, extension, analytics, extension, captcha, extension, dashboard, extension, feed, extension, fraud, extension, menu, extension, module, extension, payment, extension, promotion, extension, report, extension, shipping, extension, theme, extension, total]
 *
 *
 * TODO:
 * Поддержка type = 'theme'
 */
class Builder
{
    private $config;
    private $builder;
    private $distDir = 'dist';

    /**
     * @throws Exception
     */
    public function __construct($config_file = 'config.json')
    {
        $config = file_get_contents($config_file);
        $config = json_decode($config, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in config.json');
        }

        $this->config = $config;
        $this->config['distDir'] = $this->distDir;

        $this->validateConfig();

        $this->config['lowerName'] = strtolower($this->config['name']);
        $this->config['camelName'] = implode('', array_map('ucfirst', explode('_', $this->config['name'])));
        $this->config['ucName'] = str_replace('_', ' ', ucfirst($this->config['name']));
        $this->config['lowerType'] = strtolower($this->config['type']);
        $this->config['ucType'] = ucfirst($this->config['type']);

        $this->prepareDirs();

        require("types/{$this->config['lowerType']}.php");
        $this->builder = new $this->config['ucType']($this->config);
    }


    public function run()
    {
        // admin part
        $this->builder->buildAdminController('admin.controller', [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
            'lowerType' => $this->config['lowerType'],
            'lowerName' => $this->config['lowerName'],
        ]);
        $this->builder->buildAdminLang('admin.language.en-gb', [
            'ucType' => $this->config['ucType'],
            'ucName' => $this->config['ucName'],
            'lowerType' => $this->config['lowerType'],
            'lowerName' => $this->config['lowerName'],
        ]);
        $this->builder->buildAdminLang('admin.language.ru-ru', [
            'ucType' => $this->config['ucType'],
            'ucName' => $this->config['ucName'],
            'lowerType' => $this->config['lowerType'],
            'lowerName' => $this->config['lowerName'],
        ]);
        $this->builder->buildAdminModel('admin.model', [
            'ucType' => $this->config['ucType'],
            'camelName' => $this->config['camelName'],
        ]);
        $this->builder->buildAdminTwig('admin.view.template', [
            'lowerType' => $this->config['lowerType'],
            'lowerName' => $this->config['lowerName'],
        ]);

        //catalog part
        $this->builder->buildCatalogController('catalog.controller', []);
        $this->builder->buildCatalogModel('catalog.model', []);
        $this->builder->buildCatalogLang('catalog.language.en-gb', []);
        $this->builder->buildCatalogLang('catalog.language.ru-ru', []);
        $this->builder->buildCatalogTwig('catalog.view.theme.default.template', []);

        $this->builder->buildInstallXml('install', []);

        $filename = $this->config['lowerName'] . '.ocmod.zip';
        $zip = new ZipArchive();
        $ret = $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($ret !== TRUE) {
            printf('Ошибка с кодом %d', $ret);
        } else {
            $files = self::findAllFiles($this->config['distDir']);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;

                $f = dirname(__FILE__) . DIRECTORY_SEPARATOR . $file;
                $zip->addFile($f, str_replace($this->config['distDir'] . DIRECTORY_SEPARATOR, '', $file));
            }
            $zip->close();
        }

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Content-length: 0");// . filesize($filename));
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($filename);
        unlink($filename);
    }

    /**
     * @throws Exception
     */
    private function prepareDirs()
    {
        if (file_exists($this->distDir)) self::recursiveRemoveDir($this->distDir);
        mkdir($this->distDir);

        $commonDirs = [
            'admin/controller/extension/' . $this->config['type'],
            'admin/model/extension/' . $this->config['type'],
            'admin/language/en-gb/extension/' . $this->config['type'],
            'admin/language/ru-ru/extension/' . $this->config['type'],
            'admin/view/template/extension/' . $this->config['type'],
            'catalog/controller/extension/' . $this->config['type'],
            'catalog/model/extension/' . $this->config['type'],
            'catalog/language/en-gb/extension/' . $this->config['type'],
            'catalog/language/ru-ru/extension/' . $this->config['type'],
            'catalog/view/theme/default/template/extension/' . $this->config['type'],
        ];

        foreach ($commonDirs as $dir) {
            mkdir($this->distDir . '/' . $dir, 0755, true);
        }
    }

    public static function recursiveRemoveDir($dir)
    {
        $includes = new FilesystemIterator($dir);

        foreach ($includes as $include) {

            if (is_dir($include) && !is_link($include)) {

                self::recursiveRemoveDir($include);
            } else {

                unlink($include);
            }
        }

        rmdir($dir);
    }

    private function validateConfig()
    {
        //sort order for dashboard payment report shipping total
        // dashboard_activity_width for dashboard
        // для payment пикчу в нужное место

        if (empty($this->config['name']) or !preg_match('@[a-zA-Z_]+@s', $this->config['name']))
            throw new Exception('Config parameter NAME is mandatory and should be 3-32 symbols length and contain symbols letters or underscore');

        if (!in_array($this->config['type'], ['analytics', 'advertise', 'captcha', 'dashboard', 'feed', 'fraud', 'module', 'payment', 'report', 'shipping', 'total', 'menu', 'promotion']))
            throw new Exception("Config parameter TYPE is mandatory and should be as: 'advertise', 'analytics','captcha','dashboard','feed','fraud','menu','module','payment','promotion','report','shipping','theme','total'");

    }

    private static function findAllFiles($dir): array
    {
        $root = scandir($dir);
        $result = [];
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') continue;
            if (is_file("$dir/$value")) {
                $result[] = "$dir/$value";
                continue;
            }

            foreach (self::findAllFiles("$dir/$value") as $value) {
                $result[] = $value;
            }
        }
        return $result;
    }
}
