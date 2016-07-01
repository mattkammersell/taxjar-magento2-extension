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

/**
 * Admin nexus add form
 */
namespace Taxjar\SalesTax\Block\Adminhtml\Tax\Nexus;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
    /**
     * @var string
     */
    protected $_template = 'tax/nexus/form.phtml';

    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory
     */
    protected $_fieldsetFactory;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;
    
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_country;
    
    /**
     * @var \Taxjar\SalesTax\Api\Tax\NexusRepositoryInterface
     */
    protected $_nexusRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory $fieldsetFactory
     * @param \Taxjar\SalesTax\Api\Tax\NexusRepositoryInterface $nexusRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory $fieldsetFactory,
        \Taxjar\SalesTax\Api\Tax\NexusRepositoryInterface $nexusRepository,
        array $data = []
    ) {
        $this->formKey = $context->getFormKey();
        $this->_country = $country;
        $this->_regionFactory = $regionFactory;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_nexusRepository = $nexusRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $addressId = $this->_coreRegistry->registry('nexus_address_id');

        try {
            if ($addressId) {
                $nexus = $this->_nexusRepository->get($addressId);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /* tax rate not found */
        }

        $sessionFormValues = (array)$this->_coreRegistry->registry('nexus_form_data');
        $nexusData = isset($nexus) ? $this->extractNexusData($nexus) : [];
        $formValues = array_merge($nexusData, $sessionFormValues);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        
        $countries = $this->_country->toOptionArray(false, 'US');
        unset($countries[0]);
        
        if (!isset($formValues['country_id'])) {
            $formValues['country_id'] = $this->_scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        if (!isset($formValues['region_id'])) {
            $formValues['region_id'] = $this->_scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        
        $regionCollection = $this->_regionFactory->create()->getCollection()->addCountryFilter(
            $formValues['country_id']
        );

        $regions = $regionCollection->toOptionArray();

        $legend = $this->getShowLegend() ? __('Nexus Address Information') : '';
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => $legend, 'class' => 'form-inline']);

        if (isset($formValues['id']) && $formValues['id'] > 0) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id', 'value' => $formValues['id']]
            );
            
            $fieldset->addField(
                'api_id',
                'hidden',
                ['name' => 'api_id', 'value' => $formValues['api_id']]
            );
            
            $fieldset->addField(
                'region',
                'hidden',
                ['name' => 'region', 'value' => $formValues['region']]
            );
        }

        $fieldset->addField(
            'street',
            'text',
            [
                'name' => 'street',
                'label' => __('Street Address'),
                'required' => true,
                'value' => isset($formValues['street']) ? $formValues['street'] : ''
            ]
        );
        
        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'required' => true,
                'value' => isset($formValues['city']) ? $formValues['city'] : ''
            ]
        );
        
        $fieldset->addField(
            'region_id',
            'select',
            [
                'name' => 'region_id',
                'label' => __('State/Region'),
                'value' => isset($formValues['region_id']) ? $formValues['region_id'] : '',
                'values' => $regions
            ]
        );

        $fieldset->addField(
            'country_id',
            'select',
            [
                'name' => 'country_id',
                'label' => __('Country'),
                'required' => true,
                'value' => isset($formValues['country_id']) ? $formValues['country_id'] : '',
                'values' => $countries
            ]
        );
        
        $fieldset->addField(
            'postcode',
            'text',
            [
                'name' => 'postcode',
                'label' => __('Zip/Post Code'),
                'required' => true,
                'value' => isset($formValues['postcode']) ? $formValues['postcode'] : ''
            ]
        );

        $form->setId('nexusForm');
        $form->setTitle(__('Nexus Address Information'));
        $form->setAction($this->getUrl('taxjar/nexus/save'));
        $form->setUseContainer(true);
        $form->setMethod('post');
        $this->setForm($form);
        
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Taxjar_SalesTax::tax/nexus/js.phtml')
        );

        return parent::_prepareForm();
    }
    
    /**
     * Extract nexus data in a format which is
     *
     * @param \Taxjar\SalesTax\Api\Tax\NexusRepositoryInterface $nexus
     * @return array
     */
    protected function extractNexusData($nexus)
    {
        $nexusData = [
            'id' => $nexus->getId(),
            'api_id' => $nexus->getApiId(),
            'street' => $nexus->getStreet(),
            'city' => $nexus->getCity(),
            'country_id' => $nexus->getCountryId(),
            'region' => $nexus->getRegion(),
            'region_id' => $nexus->getRegionId(),
            'region_code' => $nexus->getRegionCode(),
            'postcode' => $nexus->getPostcode()
        ];
        return $nexusData;
    }
}
