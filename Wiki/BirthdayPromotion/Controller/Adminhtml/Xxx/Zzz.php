<?php
namespace Wiki\BirthdayPromotion\Controller\Adminhtml\Xxx;

class Zzz extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    /**
    * @var \Magento\Framework\Escaper
    */
    protected $_escaper;

    /**
     * Customer
     */
    protected $_customerFactory;

    /**
     * Current Date
     */
    protected $_currentdate;

    protected $_generator;
    
    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $currentdate,
        \Magento\SalesRule\Model\Coupon\Codegenerator $generator,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_currentdate = $currentdate;
        $this->_generator = $generator;
        $this->_escaper = $escaper;
    }

    /**
    * Post user question
    *
    * @return void
    * @throws \Exception
    */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shoppingCartPriceRule = $objectManager->create('Magento\SalesRule\Model\Rule');

        $name = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/coupon_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $discount_amount = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/discount_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $valid_date = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/coupon_valid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $coupon['name'] = $name;
        $coupon['desc'] = '';
        $coupon['start'] = date('Y-m-d');
        $coupon['end'] = date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d") + $valid_date, date("Y")));
        $coupon['uses_per_coupon'] = '1';
        $coupon['max_redemptions'] = '1';
        $coupon['discount_type'] = 'by_percent';
        $coupon['discount_amount'] = $discount_amount;
        $coupon['flag_is_free_shipping'] = 'no';
        $coupon['redemptions'] = '1';
        // $coupon['use_auto_generation'] = true;
        $coupon['code'] =$this->_generator->generateCode();

        $shoppingCartPriceRule->setName($coupon['name'])
                ->setDescription($coupon['desc'])
                ->setFromDate($coupon['start'])
                ->setToDate($coupon['end'])
                ->setUsesPerCoupon($coupon['uses_per_coupon'])
                ->setUsesPerCustomer($coupon['max_redemptions'])
                ->setCustomerGroupIds(array('0','1','2','3',))
                ->setIsActive('1')
                ->setSimpleAction($coupon['discount_type'])
                ->setDiscountAmount($coupon['discount_amount'])
                ->setDiscountQty(1)
                ->setApplyToShipping($coupon['flag_is_free_shipping'])
                ->setTimesUsed($coupon['redemptions'])
                ->setWebsiteIds(array('1',))
                // ->setUseAutoGeneration($coupon['use_auto_generation'])
                ->setCouponType('2')
                ->setCouponCode($coupon['code']);

        $shoppingCartPriceRule->save();
    }
}
