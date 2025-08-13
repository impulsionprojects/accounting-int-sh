<?php

namespace App\Models;

use App\Enums\ChartOfAccounts;
use App\Enums\JournalEntryType;
use App\Http\Controllers\JournalEntryController;
use Carbon\Carbon;

class EntryJournal extends BaseModel
{

    /**
     * @param $ref_type
     * @param $ref_id
     * @return mixed
     */
    public static function storeJournal( $ref_type, $ref_id, $reference = null, $description = 'Self journal entry' ): mixed
    {
        $journalId = app(JournalEntryController::class)->journalNumber();
        $journal = new JournalEntry();
        $journal->journal_id = $journalId;
        $journal->date = Carbon::now();
        $journal->reference = $reference ?? $journalId;
        $journal->description = $description;
        $journal->ref_type = $ref_type;
        $journal->ref_id = $ref_id;
        $journal->created_by = \Auth::user()->creatorId();
        $journal->save();

        return $journal->id;
    }

    /**
     * @param $journalId
     * @param $accountId
     * @param $debitAmount
     * @param $creditAmount
     * @param $description
     * @return void
     */
    public static function storeJournalItem( $journalId, $accountId, $debitAmount, $creditAmount, $description ): void
    {
        $journalItem = new JournalItem();
        $journalItem->journal = $journalId;
        $journalItem->account = $accountId;
        $journalItem->description = $description;
        $journalItem->debit = $debitAmount;
        $journalItem->credit = $creditAmount;
        $journalItem->save();
    }

    /**
     * @param $type
     * @param $ref_id
     * @param $journalData
     * @param $isUpdate
     * @return void
     */
    public static function journalMechanism( $type, $ref_id, $journalData, $isUpdate = false ): void
    {
        if ( $isUpdate ) {

        } else {
            if ( $type == JournalEntryType::INVOICE ) {

                $journalId = self::storeJournal($type, $ref_id);

                $finalAmount = ( $journalData['totalAmount'] + $journalData['totalTax'] - $journalData['totalDiscount'] );
                self::storeJournalItem($journalId, ChartOfAccounts::ACCOUNTS_RECEIVABLE, $finalAmount, 0, 'Invoice Created - Accounts Receivable',);

                if ( $journalData['totalTax'] > 0 ) {
                    self::storeJournalItem($journalId, ChartOfAccounts::VAT_PAYABLE, 0, $journalData['totalTax'], 'Invoice Vat Payable',);
                }

                if ( $journalData['totalDiscount'] > 0 ) {
                    self::storeJournalItem($journalId, ChartOfAccounts::SALES_DISCOUNT, $journalData['totalDiscount'], 0, 'Invoice Discount',);
                }

                foreach ( $journalData['items'] as $item ) {
                    self::storeJournalItem($journalId, $item['account'], 0, $item['price'], 'Invoice Revenue',);
                }
            }
        }
    }

    /**
     * @param $previousItems
     * @param $items
     * @return array
     */

    public static function prepareJournalData( $previousItems, $items ): array
    {

        $journalData = [];
        $journalData['previousTax'] = 0;
        $journalData['currentTax'] = 0;
        $journalData['previousDiscount'] = 0;
        $journalData['currentDiscount'] = 0;
        $journalData['newItems'] = [];
        $journalData['diffItems'] = [];
        $journalData['removeItems'] = [];

        foreach ( $previousItems as $key => $previousItem ) {
            $journalData['previousTax'] += !empty($previousItem->tax) ? Utility::taxCalculation($previousItem) : 0;
            $journalData['previousDiscount'] += isset($previousItem->discount) ? floatval($previousItem->discount) : 0;

            foreach ( $items as $i => $newItem ) {
                $product_id = !empty($newItem['item_id']) ? $newItem['item_id'] : InvoiceProduct::find($newItem['id'])->product_id;

                if ( $previousItem->product_id == $product_id ) {

                    $previousAmount = $previousItem->price * $previousItem->quantity;
                    $currentAmount = ( $newItem['price'] * $newItem['quantity'] );

                    if ( $previousAmount != $currentAmount ) {
                        $journalData['diffItems'][] = [ 'product_id' => $product_id, 'previous_amount' => $previousAmount, 'current_amount' => $currentAmount ];
                    }

                    $journalData['currentTax'] += !empty($newItem['tax']) ? Utility::taxCalculation($newItem) : 0;
                    $journalData['currentDiscount'] += isset($newItem['discount']) ? floatval($newItem['discount']) : 0;

                    unset($items[$i]);
                    unset($previousItems[$key]);
                    break;
                }
            }
        }

        foreach ( $items as $newItem ) {
            $product_id = !empty($newItem['item_id']) ? $newItem['item_id'] : InvoiceProduct::find($newItem['id'])->product_id;
            $journalData['newItems'][] = [ 'product_id' => $product_id, 'amount' => ( $newItem['price'] * $newItem['quantity'] ) ];
            $journalData['currentTax'] += !empty($newItem['tax']) ? Utility::taxCalculation($newItem) : 0;
            $journalData['currentDiscount'] += isset($newItem['discount']) ? floatval($newItem['discount']) : 0;

        }

        foreach ( $previousItems as $previousItem ) {
            $journalData['removeItems'][] = [ 'id' => $previousItem->id, 'product_id' => $previousItem->product_id, 'amount' => ( $previousItem->price * $previousItem->quantity ) ];
        }

        return $journalData;

    }

    /**
     * @param $type
     * @param $ref_id
     * @param $previousItems
     * @param $items
     * @return void
     */
    public static function journalEntryForInvoiceUpdate( $type, $ref_id, $previousItems, $items ): void
    {
        $journalData = self::prepareJournalData($previousItems, $items);


        $journalId = self::storeJournal($type, $ref_id, 'update', 'Self entry for invoice update');

        $debitVatAmount = 0;
        $creditVatAmount = 0;

        $debitDiscountAmount = 0;
        $creditDiscountAmount = 0;

        $debitItemAmountTotal = 0;
        $creditItemAmountTotal = 0;

        if ( $journalData['previousTax'] != $journalData['currentTax'] ) {

            $debitVatAmount = $journalData['previousTax'] > $journalData['currentTax'] ? $journalData['previousTax'] - $journalData['currentTax'] : 0;
            $creditVatAmount = $journalData['currentTax'] > $journalData['previousTax'] ? $journalData['currentTax'] - $journalData['previousTax'] : 0;;

            self::storeJournalItem($journalId, ChartOfAccounts::VAT_PAYABLE, $debitVatAmount, $creditVatAmount, 'Invoice Vat Payable');
        }

        if ( $journalData['previousDiscount'] != $journalData['currentDiscount'] ) {
            $debitDiscountAmount = $journalData['currentDiscount'] > $journalData['previousDiscount'] ? $journalData['currentDiscount'] - $journalData['previousDiscount'] : 0;
            $creditDiscountAmount = $journalData['previousDiscount'] > $journalData['currentDiscount'] ? $journalData['previousDiscount'] - $journalData['currentDiscount'] : 0;
            self::storeJournalItem($journalId, ChartOfAccounts::SALES_DISCOUNT, $debitDiscountAmount, $creditDiscountAmount, 'Invoice Discount');
        }

        if ( count($journalData['newItems']) > 0 ) {
            foreach ( $journalData['newItems'] as $item ) {
                $accountId = ProductService::find($item['product_id'])->account;
                $creditItemAmountTotal = $creditItemAmountTotal + $item['amount'];
                self::storeJournalItem($journalId, $accountId, 0, $item['amount'], 'Invoice Revenue');
            }
        }

        if ( count($journalData['removeItems']) > 0 ) {
            foreach ( $journalData['removeItems'] as $item ) {
                $accountId = ProductService::find($item['product_id'])->account;
                $debitItemAmountTotal = $debitItemAmountTotal + $item['amount'];
                self::storeJournalItem($journalId, $accountId, $item['amount'], 0, 'Invoice Revenue');
            }
        }

        if ( count($journalData['diffItems']) > 0 ) {
            foreach ( $journalData['diffItems'] as $item ) {
                $accountId = ProductService::find($item['product_id'])->account;
                $creditItemAmount = $item['current_amount'] > $item['previous_amount'] ? $item['current_amount'] - $item['previous_amount'] : 0;
                $debitItemAmount = $item['previous_amount'] > $item['current_amount'] ? $item['previous_amount'] - $item['current_amount'] : 0;

                $creditItemAmountTotal = $creditItemAmountTotal + $creditItemAmount;
                $debitItemAmountTotal = $debitItemAmountTotal + $debitItemAmount;

                self::storeJournalItem($journalId, $accountId, $debitItemAmount, $creditItemAmount, 'Invoice Revenue');
            }
        }

        $totalDebitAmount = $debitItemAmountTotal + $debitVatAmount + $debitDiscountAmount;
        $totalCreditAmount = $creditItemAmountTotal + $creditVatAmount + $creditDiscountAmount;

        $amountARCreate = $totalDebitAmount > $totalCreditAmount ? $totalDebitAmount - $totalCreditAmount : 0;
        $amountARDebit = $totalCreditAmount > $totalDebitAmount ? $totalCreditAmount - $totalDebitAmount : 0;


        self::storeJournalItem($journalId, ChartOfAccounts::ACCOUNTS_RECEIVABLE, $amountARDebit, $amountARCreate, 'Account Receiveable');

    }

    /**
     * @param $type
     * @param $ref_id
     * @param $accountId
     * @param $amount
     * @return void
     */
    public static function invoicePaymentJournal( $type, $ref_id, $accountId, $amount ) : void
    {
        $journalId = self::storeJournal($type, $ref_id, 'Invoice Payment', 'Self entry for invoice payment');

        $chartOfAccount = BankAccount::find($accountId)->account;

        self::storeJournalItem($journalId, ChartOfAccounts::ACCOUNTS_RECEIVABLE, 0, $amount, 'Account Receiveable credit for payment');
        self::storeJournalItem($journalId, $chartOfAccount,  $amount, 0, 'Entry for corrosponding account');
    }

    /***
     * @param $type
     * @param $ref_id
     * @param $payment
     * @return void
     */
    public static function diverseInvoicePayment($type, $ref_id, $payment) : void
    {
        $journalId = self::storeJournal($type, $ref_id, 'Invoice Payment Destroy', 'Self entry for invoice payment destroy');

        $chartOfAccount = BankAccount::find($payment->account_id)->account;

        self::storeJournalItem($journalId, ChartOfAccounts::ACCOUNTS_RECEIVABLE, $payment->amount, 0, 'Account Receivable debit for payment');
        self::storeJournalItem($journalId, $chartOfAccount,  0, $payment->amount,  'Entry for corrosponding account');
    }

}
