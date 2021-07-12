<?php
require 'commonType.php';

class OrderedType extends CommonType
{
    public function __construct($config)
    {
        parent::__construct($config);

    }

    protected function buildControllerFieldsInit(): string
    {
        $data = parent::buildControllerFieldsInit();

        $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_sort_order";
        $data .= '
        if (isset($this->request->post[\'' . $name . '\'])) {
            $data[\'' . $name . '\'] = $this->request->post[\'' . $name . '\'];
        } else {
            $data[\'' . $name . '\'] = $this->config->get(\'' . $name . '\');
        }
            ';

        return $data;
    }

    protected function buildTwigFields(): string
    {
        $data = parent::buildTwigFields();

        $name = "{$this->config['lowerType']}_{$this->config['lowerName']}_sort_order";
        $data .= '<div class="form-group">
            <label class="col-sm-2 control-label" for="input-' . $name . '">{{ entry_' . $name . ' }}</label>
            <div class="col-sm-10">
              <input type="text" name="' . $name . '" value="{{ ' . $name . ' }}" placeholder="{{ entry_' . $name . ' }}" id="input-' . $name . '" class="form-control" />
            </div>
          </div>';

        return $data;
    }
}