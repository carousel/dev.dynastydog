<?php

use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;

class PaymentsController extends BaseController {

    protected $csrf_whitelist = [ 'postPaypalIpn' ];

    public function postPaypalIpn()
    {
        $listener = new Listener;

        switch (Config::get('paypal.verifier'))
        {
            case 'fsockopen':
                $verifier = new SocketVerifier;
                break;
            
            case 'curl':
            default:
                $verifier = new CurlVerifier;
                break;
        }

        $ipnMessage = Message::createFromGlobals(); // uses php://input
        $verifier->setIpnMessage($ipnMessage);

        // https://github.com/mike182uk/paypal-ipn-listener/issues/9
        $verifier->forceSSLv3(false);

        $environment = Config::get('paypal.environment');
        $verifier->setEnvironment($environment); // can either be sandbox or production

        $listener->setVerifier($verifier);

        $listener->listen(function() use ($listener, $ipnMessage)
        {
            // On verified IPN
            $response = $listener->getVerifier()->getVerificationResponse();

            if ($this->verifyReceiverEmail($ipnMessage['receiver_email']))
            {
                // We need to log the transactions in the database in the payments table
                $paymentStatus = $ipnMessage['payment_status'];

                // Make sure it's a unique transaction
                if ($this->isUniqueTransaction($ipnMessage['txn_id'], $paymentStatus))
                {
                    $payment = Payment::logPaypalIpn($ipnMessage);

                    $itemNumber = $ipnMessage['item_number'];

                    switch ($itemNumber)
                    {
                        case 'credit_package':
                            $this->processCreditPurchaseTransaction($payment, $listener);
                            break;
                        
                        default:
                            break;
                    }
                }
            }
            else
            {
                $report = $listener->getReport();

                Log::error('PAYPAL_INVALID_RECEIVER_EMAIL', array('report' => $report));
            }
        }, function() use ($listener, $ipnMessage)
        {
            // On invalid IPN
            $report   = $listener->getReport();
            $response = $listener->getVerifier()->getVerificationResponse();

            Log::error('PAYPAL_INVALID_IPN', array('report' => $report));
        });
    }

    protected function verifyReceiverEmail($receiverEmail)
    {
        return ($receiverEmail === Config::get('paypal.receiver_email'));
    }

    protected function isUniqueTransaction($transactionId, $paymentStatus)
    {
        // See if this payment exists already in this state
        $payment = Payment::where('transaction_id', $transactionId)
                 ->where('payment_status', $paymentStatus)
                 ->first();

        return is_null($payment);
    }

    protected function processCreditPurchaseTransaction($payment, $listener)
    {
        if ($payment->isCompleted())
        {
            try
            {
                DB::transaction(function() use ($payment)
                {
                    // Get the user
                    $user = User::find($payment->ipn_message['custom']);

                    if ( ! is_null($user))
                    {
                        // Attach the payment to the user who bought the credits
                        $user->payments()->attach($payment->id);

                        // Find the credit package
                        $package = CreditPackage::where('name', $payment->ipn_message['option_selection1'])->first();

                        if ( ! is_null($package))
                        {
                            $user->credits += $package->credit_amount;
                            $user->save();

                            // Attach the package to the payment
                            $payment->creditPackages()->attach($package->id);
                        }
                        else
                        {
                            throw new Exception('PAYPAL_CREDIT_PACKAGE_NOT_FOUND');
                        }
                    }
                    else
                    {
                        throw new Exception('PAYPAL_USER_NOT_FOUND');
                    }
                });   
            }
            catch(Exception $e)
            {
                Log::error($e->getMessage(), array('report' => $listener->getReport()));
            }
        }
        else
        {
            Log::error('PAYPAL_TRANSACTION_NOT_COMPLETED', array('report' => $listener->getReport()));
        }
    }

}
