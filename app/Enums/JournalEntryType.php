<?php

namespace App\Enums;

abstract class JournalEntryType
{
    const INVOICE = 1;

    const INVOICE_UPDATE = 2;

    const INVOICE_PAYMENT = 3;

    const BILL = 4;

    const BILL_UPDATE = 5;

    const BILL_PAYMENT = 6;

}
