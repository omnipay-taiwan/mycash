<?php

namespace Omnipay\MyCash\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\MyCash\Traits\HasCreditCard;
use Omnipay\MyCash\Traits\HasCVS;
use Omnipay\MyCash\Traits\HasDefaults;
use Omnipay\MyCash\Traits\HasMyCash;

class PurchaseRequest extends AbstractRequest
{
    use HasMyCash;
    use HasDefaults;
    use HasCreditCard;
    use HasCVS;

    public function getChoosePayment()
    {
        $lookup = [
            'atm' => 'ATM',
            'cvs' => 'CVS',
            'barcode' => 'BARCODE',
            'funpoint' => 'FunPoint',
        ];
        $choosePayment = strtolower($this->getParameter('ChoosePayment') ?? '');

        return array_key_exists($choosePayment, $lookup) ? $lookup[$choosePayment] : 'CreditCard';
    }

    public function setChoosePayment($value)
    {
        return $this->setParameter('ChoosePayment', $value);
    }

    public function getData()
    {
        $this->validate('HashKey', 'HashIV', 'transactionId', 'amount', 'description', 'MerProductID', 'MerUserID', 'ItemName');

        $common = [
            'HashKey' => $this->getHashKey(),
            'HashIV' => $this->getHashIV(),
            'MerTradeID' => $this->getTransactionId(),
            'MerProductID' => $this->getMerProductID(),
            'MerUserID' => $this->getMerUserID(),
        ];

        $choosePayment = $this->getChoosePayment();

        if ($choosePayment === 'ATM') {
            return array_merge($common, [
                'Amount' => (int) $this->getAmount(),
                'TradeDesc' => $this->getDescription(),
                'ItemName' => $this->getItemName(),
            ]);
        }

        if (in_array($choosePayment, ['CVS', 'BARCODE', 'FunPoint'], true)) {
            return array_filter(array_merge($common, [
                'ChoosePayment' => $this->getChoosePayment(),
                'ChooseStoreID' => $this->getChooseStoreID(),
                'Amount' => (int) $this->getAmount(),
                'TradeDesc' => $this->getDescription(),
                'ItemName' => $this->getItemName(),
            ]), static function ($value) {
                return ! empty($value);
            });
        }

        return array_merge($common, [
            'Amount' => (int) $this->getAmount(),
            'TradeDesc' => $this->getDescription(),
            'ItemName' => $this->getItemName(),
            'UnionPay' => $this->getUnionPay(),
            'Installment' => $this->getInstallment(),
        ]);
    }

    public function sendData($data): PurchaseResponse
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
