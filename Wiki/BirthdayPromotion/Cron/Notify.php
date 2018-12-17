<?php


namespace Wiki\BirthdayPromotion\Cron;

class Notify
{

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob Notify is executed.");
        /* Here we prepare data for our email  */
 
 
        /* Receiver Detail  */
        $receiverInfo = [
            'name' => 'Reciver Name',
            'email' => 'receiver@address.com'
        ];
        
        
        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Sender Name',
            'email' => 'sender@addess.com',
        ];
        
        
        /* Assign values for your template variables  */
        $emailTemplateVariables = array();
        $emailTempVariables['myvar1'] = $variablevalue1;
        $emailTempVariables['myvar2'] = $variablevalue2;
        /* We write send mail function in helper because if we want to 
        use same in other action then we can call it directly from helper */ 
        
        /* call send mail method from helper or where you define it*/ 
        $this->_objectManager->get('Wiki\BirthdayPromotion\Helper\Email')->yourCustomMailSendMethod(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }
}
