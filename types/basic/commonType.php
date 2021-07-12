<?php


class CommonType
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function build(string $template, array $params, $ext = 'php'): void
    {
        $templateResult = $this->getTemplate($template);

        foreach ($params as $key => $value) {
            $templateResult = str_replace("%#{$key}#%", $value, $templateResult);
        }

        $path = str_replace('.', '/', $template);
        file_put_contents("{$this->config['distDir']}/{$path}/extension/{$this->config['lowerType']}/{$this->config['lowerName']}.{$ext}", $templateResult);
    }

    public function buildAdminController(string $template, array $params): void
    {
//        if ($this->isTextAreaAppears()) {
        $params['languages'] = '
        $this->load->model(\'localisation/language\');
        $data[\'languages\'] = $this->model_localisation_language->getLanguages();';
//        } else $params['languages'] = '';

        $params['fields_init'] = $this->buildControllerFieldsInit();
        $params['fields_values'] = $this->buildControllerFieldsValues();
        $params['fields_validation'] = $this->buildControllerFieldsValidators();
        $params['fields_errors'] = $this->buildControllerFieldsErrors();

        $this->build($template, $params);
    }

    public function buildAdminModel(string $template, array $params): void
    {

        $params['fields_init'] = $this->buildModelFieldsInit();

        $this->build($template, $params);
    }

    public function buildAdminTwig(string $template, array $params): void
    {
        $params['fields'] = $this->buildTwigFields();

        $this->build($template, $params, 'twig');
    }

    public function buildAdminLang(string $template, array $params): void
    {
        $params['fields_help'] = $this->buildLangFields('help');
        $params['fields_entry'] = $this->buildLangFields('entry');
        $params['fields_error'] = $this->buildLangFields('error');

        $this->build($template, $params);
    }

    public function buildCatalogController(string $template, array $params): void
    {
    }

    public function buildCatalogModel(string $template, array $params): void
    {
    }

    public function buildCatalogLang(string $template, array $params): void
    {
    }

    public function buildCatalogTwig(string $template, array $params): void
    {
    }

    public function buildInstallXml(string $template, array $params): void
    {
        $params['name'] = $this->config['lowerName'];
        $params['label'] = $this->config['label'] ?? $this->config['ucName'];
        $params['author'] = $this->config['author'] ?? '';
        $params['link'] = $this->config['link'] ?? '';

        $templateResult = $this->getTemplate($template);

        foreach ($params as $key => $value) {
            $templateResult = str_replace("%#{$key}#%", $value, $templateResult);
        }

        file_put_contents("{$this->config['distDir']}/install.xml", $templateResult);
    }

    protected function buildTwigFields(): string
    {
        $data = '';
        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            $required = !empty($field['required']) ? ' required' : '';
            $tooltip = !empty($field['hint']) ? '<span data-toggle="tooltip" title="{{ help_' . $name . ' }}"></span>' : '';

            switch ($field['type']) {
                case 'number':
                    $data .= '
                    <div class="form-group' . $required . '">
                        <label for="input-' . $name . '" class="col-sm-2 control-label">{{ entry_' . $name . ' }}' . $tooltip . '</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="input-' . $name . '" name="' . $name . '" value="{{ ' . $name . ' }}"' . $required . ' />
                            {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
                        </div>
                    </div>';
                    break;
                case 'select':
                    $data .= '
                    <div class="form-group' . $required . '">
                        <label class="col-sm-2 control-label" for="input-' . $name . '">{{ entry_' . $name . ' }}' . $tooltip . '</label>
                        <div class="col-sm-10">
                            <select name="' . $name . '" id="input-' . $name . '" class="form-control"' . $required . '>
                                {% for ' . $name . '_key,' . $name . '_value in ' . $name . '_items %}
                                    {% if ' . $name . '_key == ' . $name . ' %}
                                        <option value="{{ ' . $name . '_key }}" selected="selected">{{ ' . $name . '_value }}</option>
                                    {% else %}
                                        <option value="{{ ' . $name . '_key }}">{{ ' . $name . '_value }}</option>
                                    {% endif %}
                                {% endfor %}
                            </select>
                            {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
                        </div>
                    </div>';
                    break;
                case 'hidden':
                    $data .= '
                    <input type="hidden" value="{{ ' . $name . '_value }}" name="' . $name . '" id="' . $name . '"/>';
                    break;
                case 'radio':
                    $data .= '
                    <div class="form-group' . $required . '">
                        <label class="col-sm-2 control-label">{{ entry_' . $name . ' }}' . $tooltip . '</label>
                        <div class="col-sm-10">
                            {% for ' . $name . '_key,' . $name . '_value in ' . $name . '_items %}
                                <label class="radio-inline">
                                    <input type="radio" name="' . $name . '" value="{{ ' . $name . '_key }}"{% if ' . $name . '_key == ' . $name . ' %} checked="checked"{% endif %}' . $required . ' />
                                    {{ ' . $name . '_value }}
                                </label>
                            {% endfor %}
                            {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
                        </div>
                    </div>';
                    break;
                case 'checkbox':
                    $data .= '
                    <div class="form-group' . $required . '">
						<label class="col-sm-2 control-label" for="input-' . $name . '">{{ entry_' . $name . ' }}' . $tooltip . '</label>
						<div class="col-sm-10 anyplace">
							{% if ' . $name . ' %}
							    <input type="checkbox" name="' . $name . '" value="1" checked="checked" class="form-control pull-left"/>
							{% else %}
							    <input type="checkbox" name="' . $name . '" class="form-control pull-left"/>
							{% endif %}
							 {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
						</div>
					</div>';
                    break;
                case 'checkboxlist':
                    $data .= '
                    <div class="form-group' . $required . '">
                        <label class="col-sm-2 control-label" for="input-' . $name . '">{{ entry_' . $name . ' }}' . $tooltip . '</label>
                        <div class="col-sm-10">
                            <div class="well well-sm" style="height: 150px; overflow: auto;">
                                {% for ' . $name . '_key,' . $name . '_value in ' . $name . '_items %}
                                    <div class="checkbox">
                                        <label>
                                        {% if ' . $name . '_key in ' . $name . ' %}
                                            <input type="checkbox" name="' . $name . '[{{ ' . $name . '_key }}]" value="{{ ' . $name . '_key }}" checked="checked" />
                                            {{ ' . $name . '_value }}
                                        {% else %}
                                            <input type="checkbox" name="' . $name . '[{{ ' . $name . '_key }}]" value="{{ ' . $name . '_key }}" />
                                            {{ ' . $name . '_value }}
                                        {% endif %}
                                        </label>
                                    </div>
                                {% endfor %}
                            </div>
                            {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
                        </div>
                    </div>  ';
                    break;
                case 'string':
                default:
                    $data .= '
                    <div class="form-group' . $required . '">
                        <label for="input-' . $name . '" class="col-sm-2 control-label">{{ entry_' . $name . ' }}' . $tooltip . '</label>
                        <div class="col-sm-10">
                            <input type="text" id="input-' . $name . '" class="form-control" name="' . $name . '" value="{{ ' . $name . ' }}"' . $required . ' />
                            {% if error_' . $name . ' %}
                                <div class="text-danger">{{ error_' . $name . ' }}</div>
                            {% endif %}
                        </div>
                    </div>';
                    break;
            }
        }

        return $data;
    }

    protected function isTextAreaAppears(): bool
    {
        foreach ($this->config['fields'] as $field) {
            if ($field['type'] === 'textarea') return true;
        }

        return false;
    }

    protected function getTemplate(string $template)
    {
        return file_get_contents("templates/{$template}.btp");
    }

    protected function buildControllerFieldsInit(): string
    {
        $data = '';

        $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_status";
        $data .= '
        if (isset($this->request->post[\'' . $name . '\'])) {
            $data[\'' . $name . '\'] = $this->request->post[\'' . $name . '\'];
        } else {
            $data[\'' . $name . '\'] = $this->config->get(\'' . $name . '\');
        }';

        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            $data .= '
        if (isset($this->request->post[\'' . $name . '\'])) {
            $data[\'' . $name . '\'] = $this->request->post[\'' . $name . '\'];
        } else {
            $data[\'' . $name . '\'] = $this->config->get(\'' . $name . '\');
        }';
            if (!empty($field['defaultValue'])) {
                $default = $field['defaultValue'];
                if (is_array($field['defaultValue'])) {
                    $default = [];
                    foreach ($field['defaultValue'] as $value) {
                        $default[] = [$value => $field['values'][$value]];
                    }
                    $default = json_encode($default);
                }
                $data .= '
        if ($data[\'' . $name . '\'] === null) {
            $data[\'' . $name . '\'] = \'' . $default . '\';
        }';
            }
        }

        return $data;
    }

    protected function buildModelFieldsInit(): string
    {
        $data = '';
        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            $camelName = lcfirst(implode('', array_map('ucfirst', explode('_', $name))));

            if (in_array($field['type'], ['select', 'radio', 'checkboxlist'])) {
                $values = [];
                foreach ($field['values'] as $value_key => $value_val) {
                    $values[] = "            '{$value_key}' => '{$value_val}'";
                }
                $data .= '
    public function ' . $camelName . 'Items()
    {
        return [
' . implode(",\n", $values) . '
        ];
    }
                    ';
            }
        }

        return $data;
    }

    protected function buildControllerFieldsValues(): string
    {
        $data = '';
        $model = '';
        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            $camelName = lcfirst(implode('', array_map('ucfirst', explode('_', $name))));

            if (in_array($field['type'], ['select', 'radio', 'checkboxlist'])) {
                $model = '$this->load->model(\'extension/' . $this->config['lowerType'] . '/' . $this->config['lowerName'] . '\');' . "\n";
                $data .= '
        $data[\'' . $name . '_items\'] = $this->model_extension_' . $this->config['lowerType'] . '_' . $this->config['lowerName'] . '->' . $camelName . 'Items();
                ';
            }
        }

        return $model . $data;
    }

    protected function buildLangFields(string $type): string
    {
        $data = '';

        foreach ($this->config['fields'] as $field) {
            $name = "{$type}_{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            if ($type == 'help') {
                if (!empty($field['hint']))
                    $data .= '
$_[\'' . $name . '\']      = \'' . $field['hint'] . '\';';
            }
            if ($type == 'error') {
                if (!empty($field['validation'])) {
                    foreach ($field['validation'] as $valid_key => $valid_val) {
                        $data .= '
$_[\'' . $name . '_' . $valid_key . '\']      = \'Validation error, invalid ' . $valid_key . '\';';
                    }
                }
                if (!empty($field['required'])) {
                    $data .= '
$_[\'' . $name . '_mandatory\']      = \'Validation error, field is mandatory\';';
                }
            }
            if ($type == 'entry') {
                if (!empty($field['label']))
                    $data .= '
$_[\'' . $name . '\']      = \'' . $field['label'] . '\';';
            }
        }

        return $data;
    }

    protected function buildControllerFieldsErrors(): string
    {
        $data = '';

        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            if (!empty($field['validation']) or !empty($field['required'])) {
                $data .= '
        if (isset($this->error[\'' . $name . '\'])) {
            $data[\'error_' . $name . '\'] = $this->error[\'' . $name . '\'];
        } else {
            $data[\'error_' . $name . '\'] = \'\';
        }';
            }
        }

        return $data;

    }

    protected function buildControllerFieldsValidators(): string
    {
        $data = '';

        foreach ($this->config['fields'] as $field) {
            $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_{$field['name']}";
            if (!empty($field['validation'])) {
                foreach ($field['validation'] as $validator => $value) {
                    switch ($validator) {
                        case 'min':
                            $data .= '
        if (floatval($this->request->post[\'' . $name . '\']) < ' . $value . ') {
            $this->error[\'' . $name . '\'] = $this->language->get(\'error_' . $name . '_' . $validator . '\');
        }';
                            break;
                        case 'max':
                            $data .= '
        if (floatval($this->request->post[\'' . $name . '\'])  > ' . $value . ') {
            $this->error[\'' . $name . '\'] = $this->language->get(\'error_' . $name . '_' . $validator . '\');
        }';
                            break;
                        case 'lengthMin':
                            $data .= '
        if (strlen($this->request->post[\'' . $name . '\']) < ' . $value . ') {
            $this->error[\'' . $name . '\'] = $this->language->get(\'error_' . $name . '_' . $validator . '\');
        }';
                            break;
                        case 'lengthMax':
                            $data .= '
        if (strlen($this->request->post[\'' . $name . '\']) > ' . $value . ') {
            $this->error[\'' . $name . '\'] = $this->language->get(\'error_' . $name . '_' . $validator . '\');
        }';
                            break;
                    }
                }
            }
            if (!empty($field['required'])) {
                $data .= '
        if (empty($this->request->post[\'' . $name . '\'])) {
            $this->error[\'' . $name . '\'] = $this->language->get(\'error_' . $name . '_mandatory\');
        }';
            }
        }

        return $data;
    }
}