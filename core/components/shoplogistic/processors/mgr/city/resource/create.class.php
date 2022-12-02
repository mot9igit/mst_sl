<?php
class slCityResourceCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'slCityResource';
    
    /**
     * var modX
     */
    public function beforeSet() {
        $city = trim($this->getProperty('city'));
        $resource = trim($this->getProperty('resource'));

        if ($this->modx->getCount($this->classKey, ['city' => $city, 'resource' => $resource])) {
            $this->modx->error->addField('city', $this->modx->lexicon('shoplogistic_resource_domain_ae'));
        }
        
        return parent::beforeSet();
    }
}

return "slCityResourceCreateProcessor";