<?php


require_once(dirname(__FILE__) . '/update.class.php');

class slExportFilesCatsOptionsUpdateFromGridProcessor extends slExportFilesCatsOptionsUpdateProcessor
{

    /**
     * @param modX $modx
     * @param string $className
     * @param array $properties
     *
     * @return modProcessor
     */
    public static function getInstance(modX &$modx, $className, $properties = [])
    {
        /** @var modProcessor $processor */
        $processor = new slExportFilesCatsOptionsUpdateFromGridProcessor($modx, $properties);

        return $processor;
    }

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        $data = json_decode($data, true);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        $data = $this->prepareValues($data);
        $this->setProperties($data);
        $this->unsetProperty('data');

        return parent::initialize();
    }

    public function prepareValues($data){
        if(isset($data['opt'])){
            $data["option_id"] = $data['opt'];
        }
        // $this->modx->log(1, print_r($data, 1));
        return $data;
    }
}

return 'slExportFilesCatsOptionsUpdateFromGridProcessor';