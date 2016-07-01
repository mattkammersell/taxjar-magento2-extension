<?php
/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2016 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
 
namespace Taxjar\SalesTax\Observer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Taxjar\SalesTax\Model\Configuration as TaxjarConfig;

class ConfigChanged implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @param CacheInterface $cache
     * @param ManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CacheInterface $cache,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_cache = $cache;
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
    }
    
    /**
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->_updateSmartcalcs();
        $this->_updateBackupRates();
        return $this;
    }
    
    /**
     * @return void
     */
    private function _updateSmartcalcs()
    {
        $enabled = (int)$this->_scopeConfig->getValue(TaxjarConfig::TAXJAR_ENABLED); 
        $prevEnabled = (int)$this->_cache->load('taxjar_salestax_config_enabled');

        if (isset($prevEnabled)) {
            if ($prevEnabled != $enabled && $enabled == 1) {
                $this->_eventManager->dispatch('taxjar_salestax_import_data');
            }
        }
    }
    
    /**
     * @return void
     */
    private function _updateBackupRates()
    {
        $enabled = (int)$this->_scopeConfig->getValue(TaxjarConfig::TAXJAR_BACKUP); 
        $prevEnabled = (int)$this->_cache->load('taxjar_salestax_config_backup');

        if (isset($prevEnabled)) {
            if ($prevEnabled != $enabled) {
                $this->_eventManager->dispatch('taxjar_salestax_import_data');
                $this->_eventManager->dispatch('taxjar_salestax_import_rates');
            }
        }
    }
}