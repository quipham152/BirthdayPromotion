<?php


namespace Wiki\BirthdayPromotion\Cron;

class Notify
{
    const EMAIL_SENDER = 'birthdaypromotion/general/email_sender_to';
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

    protected $logger;

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
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $currentdate,
        \Magento\SalesRule\Model\Coupon\Codegenerator $generator,
        \Magento\Framework\Escaper $escaper
    )
    {
        $this->logger = $logger;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_currentdate = $currentdate;
        $this->_generator = $generator;
        $this->_escaper = $escaper;
    }

    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }

    public function generateCoupon($use_per_coupon){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shoppingCartPriceRule = $objectManager->create('Magento\SalesRule\Model\Rule');

        $name = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/coupon_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $discount_amount = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/discount_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $valid_date = $this->scopeConfig->getValue('birthdaypromotion/birthday_coupon/coupon_valid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $coupon['name'] = $name;
        $coupon['desc'] = '';
        $coupon['start'] = date('Y-m-d');
        $coupon['end'] = date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d") + $valid_date, date("Y")));
        $coupon['uses_per_coupon'] = $use_per_coupon;
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

        return $coupon['code'];
    }
    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        try {
            
            $postObject = new \Magento\Framework\DataObject();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $date = $this->_currentdate->gmtDate('Y-m-d');
            $customerCollection = $this->getCustomerCollection();
            $error = false;

            $templateOptions = array(
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            );

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $dobs =  $customerCollection->addFieldToFilter('dob', ['eq' => $date]);
            $numsDob = count($dobs);
            $code = $this->generateCoupon($numsDob);
            foreach ($dobs as $dob) {
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('birthdaypromotion_general_email_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars([
                    'data' => $postObject,
                    'customer_name'    => $dob->getName(),
                    'birthday_code'    => $code
                    ])
                ->setFrom(
                    $this->scopeConfig->getValue(self::EMAIL_SENDER, $storeScope)
                )
                ->addTo($dob->getEmail())
                ->getTransport();
            
                $transport->sendMessage(); 
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return;
        }
    }
}
