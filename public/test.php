<h2>test logfile</h2>
<?php

class mytest
{

    public function mytest()
    {
        echo "I am in mytest";
        //$writer = new Zend\Log\Writer\Stream('/datpa/log/logfile');
        printf($writer);
        printf("here is writer");
        //throw new \Exception('Failed to create writer');
        try {
            $writer = new Zend\Log\Writer\Stream('/datpa/log/logfile');
            printf($writer);
            throw new \Exception('Failed to create writer');
            if (! $writer) {
                throw new Exception('Failed to create writer');
            }
            $logger = new Zend\Log\Logger();
            $logger->addWriter($writer);
            
            $logger->info('Informational message');
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}

$mytest = new mytest();

$mytest->mytest;
