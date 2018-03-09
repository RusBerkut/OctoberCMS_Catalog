<?php namespace Radit\Catalog\Models;

use Model;
use Radit\catalog\Models\Currency as Currency;

class Settings extends Model {

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'radit_shop_settings';

    public $settingsFields = 'fields.yaml';
    
    public function getCurrenciesList()
    {
        return Currency::lists('name', 'id');
    }
    
    public function getWmzCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }
    
    public function getWmrCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }
    
    public function getWmuCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }
    
    public function getWmeCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }
    
    public function getYadCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }

    public function getQiwiCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }

    public function getPaypalCarrencyOptions()
    {
        return $this->getCurrenciesList();
    }

}
