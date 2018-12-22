<?php
namespace Wiki\BirthdayPromotion\Controller\Adminhtml\Xxx;

class Index extends \Magento\Framework\App\Action\Action
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

    /**
     * Customer
     */
    protected $_customerFactory;

    /**
     * Current Date
     */
    protected $_currentdate;
    
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
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_currentdate = $currentdate;
        $this->_escaper = $escaper;
    }

    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }
    /**
    * Post user question
    *
    * @return void
    * @throws \Exception
    */
    public function execute()
    {
        try {
            
            $postObject = new \Magento\Framework\DataObject();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $customerCollection = $this->getCustomerCollection();
            $date = $this->_currentdate->gmtDate('Y-m-d');
            $error = false;

            $templateOptions = array(
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            );

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            foreach ($customerCollection as $customer) {
                $_birthday = $customer->getDob();// get Customer Date of birth
                if($_birthday == $date){// check Date of birth equal current date ?

                    $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('birthdaypromotion_general_email_template') // this code we have mentioned in the email_templates.xml
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom(
                        // $sender
                        $this->scopeConfig->getValue(self::EMAIL_SENDER, $storeScope)
                    )
                    ->addTo($customer->getEmail())
                    ->getTransport();
                
                    $transport->sendMessage(); 
                }else{
                }
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
