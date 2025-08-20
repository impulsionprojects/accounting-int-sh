<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;
use App\Mail\CommonEmailTemplate;
use Illuminate\Support\Facades\Mail;

class Utility extends BaseModel
{
    public static function settings()
    {
        $data = DB::table('settings');
        if(\Auth::check())
        {
            $userId = \Auth::user()->creatorId();
            $data   = $data->where('created_by', '=', $userId);
        }
        else
        {
            $data = $data->where('created_by', '=', 1);
        }
        $data     = $data->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INVO",
            "journal_prefix" => "#JUR",
            "invoice_color" => "ffffff",
            "proposal_prefix" => "#PROP",
            "retainer_prefix" => "#RET",
            "proposal_color" => "ffffff",
            "proposal_logo" => "1_proposal_logo.png",
            "retainer_logo" => "1_retainer_logo.png",
            "invoice_logo" => "1_invoice_logo.png",
            "bill_logo" => "1_bill_logo.png",
            "retainer_color" => "ffffff",
            "bill_prefix" => "#BILL",
            "bill_color" => "ffffff",
            "customer_prefix" => "#CUST",
            "vender_prefix" => "#VEND",
            "contract_prefix" => "#CON",
            "contract_template" => 'template1',
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "bill_template" => "template1",
            "proposal_template" => "template1",
            "retainer_template" => "template1",
            "registration_number" => "",
            "tax_number" => "on",
            "vat_number" => "",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "decimal_number" => "2",
            "tax_type" => "",
            "shipping_display" => "on",
            "display_landing_page" => "on",
            "title_text" => "",
            'gdpr_cookie' => "off",
            'cookie_text' => "",
            "twilio_sid" => "",
            "twilio_token" => "",
            "twilio_from" => "",
            "invoice_starting_number" =>"1",
            "proposal_starting_number" =>"1",
            "bill_starting_number" =>"1",
            "footer_text" => "",
            "company_logo_light" => "logo-light.png",
            "company_logo_dark" =>  "logo-dark.png",
            "company_favicon" => "",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "color" => "",
            "retainer_starting_number" => "1",
            "SITE_RTL" => "off",
            "storage_setting" => "",
            "local_storage_validation" => "",
            "local_storage_max_upload_size" => "",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",

        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function settingsById($id)
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', $id);
        $data     = $data->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INVO",
            "journal_prefix" => "#JUR",
            "invoice_color" => "ffffff",
            "proposal_prefix" => "#PROP",
            "proposal_color" => "ffffff",
            "proposal_logo" => "2_proposal_logo.png",
            "retainer_logo" => "2_retainer_logo.png",
            "invoice_logo" => "2_invoice_logo.png",
            "bill_logo" => "2_bill_logo.png",
            "retainer_color" => "ffffff",
            "bill_prefix" => "#BILL",
            "bill_color" => "ffffff",
            "customer_prefix" => "#CUST",
            "vender_prefix" => "#VEND",
            "contract_prefix" => "#CON",
            "retainer_prefix" => "#RET",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "bill_template" => "template1",
            "proposal_template" => "template1",
            "retainer_template" => "template1",
            "contract_template" => 'template1',
            "registration_number" => "",
            "vat_number" => "",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "decimal_number" => "2",
            "tax_number" => "on",
            "tax_type" => "",
            "shipping_display" => "on",
            "journal_prefix" => "#JUR",
            "display_landing_page" => "on",
            "title_text" => "",
            'gdpr_cookie' => "off",
            'cookie_text' => "",
            "twilio_sid" => "",
            "twilio_token" => "",
            "twilio_from" => "",
            "invoice_starting_number" =>"1",
            "proposal_starting_number" =>"1",
            "bill_starting_number" =>"1",
            "company_logo_light" => "logo-light.png",
            "company_logo_dark" =>  "logo-dark.png",
            "company_favicon" => "",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "color" => "",
            "SITE_RTL"=>"",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function getStorageSetting()
    {
        $data = DB::table('settings');

            // $userId = \Auth::user()->creatorId();
            $data   = $data->where('created_by', '=', 1);

        $data     = $data->get();


        $settings = [
            "storage_setting" => "",
            "local_storage_validation" => "",
            "local_storage_max_upload_size" => "",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function languages()
    {
        $dir     = base_path() . '/resources/lang/';
        $glob    = glob($dir . "*", GLOB_ONLYDIR);
        $arrLang = array_map(
            function ($value) use ($dir){
                return str_replace($dir, '', $value);
            }, $glob
        );
        $arrLang = array_map(
            function ($value) use ($dir){
                return preg_replace('/[0-9]+/', '', $value);
            }, $arrLang
        );
        $arrLang = array_filter($arrLang);

        return $arrLang;
    }

    public static function getGdpr()
    {
        $data = DB::table('settings');
        if (\Auth::check()) {
            $userId = \Auth::user()->creatorId();
            $data   = $data->where('created_by', '=', $userId);
        } else {
            $data = $data->where('created_by', '=', 1);
        }
        $data     = $data->get();
        $settings = [

            'gdpr_cookie' => "",
            'cookie_text' => "",

        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function getValByName($key)
    {
        $setting = Utility::settings();
        if(!isset($setting[$key]) || empty($setting[$key]))
        {
            $setting[$key] = '';
        }

        return $setting[$key];
    }

    public static function getValByName1($key)
    {
        $setting = Utility::getGdpr();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if(count($values) > 0)
        {
            foreach($values as $envKey => $envValue)
            {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                // If key does not exist, add it
                if(!$keyPosition || !$endOfLinePosition || !$oldLine)
                {
                    $str .= "{$envKey}='{$envValue}'\n";
                }
                else
                {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        if(!file_put_contents($envFile, $str))
        {
            return false;
        }

        return true;
    }

    public static function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $arr['templates'] = [
            "template1" => "New York",
            "template2" => "Toronto",
            "template3" => "Rio",
            "template4" => "London",
            "template5" => "Istanbul",
            "template6" => "Mumbai",
            "template7" => "Hong Kong",
            "template8" => "Tokyo",
            "template9" => "Sydney",
            "template10" => "Paris",
        ];

        return $arr;
    }

    public static function addNewData()
    {
        \Artisan::call('cache:forget spatie.permission.cache');
        \Artisan::call('cache:clear');
        $usr = \Auth::user();

        $arrPermissions = [
            'manage budget planner',
            'create budget planner',
            'edit budget planner',
            'delete budget planner',
            'view budget planner',
            'stock report',
            'manage contract',
            'manage customer contract',
            'create contract',
            'edit contract',
            'delete contract',
            'view contract',
            'duplicate contract',
            'delete attachment',
            'delete comment',
            'delete notes',
            'contract description',
            'upload attachment',
            'add comment',
            'add notes',
            'send contract mail',
            'manage retainer',

        ];
        foreach($arrPermissions as $ap)
        {
            // check if permission is not created then create it.
            $permission = Permission::where('name', 'LIKE', $ap)->first();
            if(empty($permission))
            {
                Permission::create(['name' => $ap]);
            }
        }
        $companyRole = Role::where('name', 'LIKE', 'company')->first();

        $companyPermissions   = $companyRole->getPermissionNames()->toArray();
        $companyNewPermission = [
            'manage budget planner',
            'create budget planner',
            'edit budget planner',
            'delete budget planner',
            'view budget planner',
            'stock report',
            'manage contract',
            'manage customer contract',
            'create contract',
            'edit contract',
            'delete contract',
            'view contract',
            'duplicate contract',
            'delete attachment',
            'delete comment',
            'delete notes',
            'contract description',
            'upload attachment',
            'add comment',
            'add notes',
            'send contract mail',
            'manage retainer',
        ];
        foreach($companyNewPermission as $op)
        {
            // check if permission is not assign to owner then assign.
            if(!in_array($op, $companyPermissions))
            {
                $permission = Permission::findByName($op);
                $companyRole->givePermissionTo($permission);
            }
        }


    }


    public static function priceFormat($settings, $price)
    {
        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, Utility::getValByName('decimal_number')) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public static function currencySymbol($settings)
    {
        return $settings['site_currency_symbol'];
    }

    public static function dateFormat($settings, $date)
    {
        return date($settings['site_date_format'], strtotime($date));
    }

    public static function timeFormat($settings, $time)
    {
        return date($settings['site_time_format'], strtotime($time));
    }

    public static function invoiceNumberFormat($settings, $number)
    {
        $settings = Utility::settings();
        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public static function proposalNumberFormat($settings, $number)
    {
        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public static function retainerNumberFormat($settings, $number)
    {
        $settings = Utility::settings();
        return $settings["retainer_prefix"] . sprintf("%05d", $number);
    }

    public static function customerProposalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public static function customerRetainerNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["retainer_prefix"] . sprintf("%05d", $number);
    }

    public static function customerInvoiceNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public static function billNumberFormat($settings, $number)
    {
        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }

    public static function vendorBillNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }

    public static function contractNumberFormat($settings, $number)
    {
        return $settings["contract_prefix"] . sprintf("%05d", $number);
    }

    public static function tax($taxes)
    {

        $taxArr = explode(',', $taxes);
        $taxes  = [];
        foreach($taxArr as $tax)
        {
            $taxes[] = Tax::find($tax);
        }

        return $taxes;
    }

    public static function taxCalculation( $item )
    {
        $taxes = self::tax($item['tax']);

        $totalTaxPrice = 0;
        foreach ( $taxes as $tax ) {
            $taxPrice = self::taxRate($tax->rate, $item['price'], $item['quantity'], $item['discount']);
            $totalTaxPrice += $taxPrice;
        }
        return $totalTaxPrice;
    }

    // public static function taxRate($taxRate, $price, $quantity)
    // {

    //     return ($taxRate / 100) * ($price * $quantity);
    // }

    public static function taxRate($taxRate, $price, $quantity,$discount=0)
    {

    //   return ($taxRate / 100) * (($price-$discount) * $quantity);
        return (($price*$quantity) - $discount) * ($taxRate /100);
    }

    public static function totalTaxRate($taxes)
    {

        $taxArr  = explode(',', $taxes);
        $taxRate = 0;

        foreach($taxArr as $tax)
        {

            $tax     = Tax::find($tax);
            $taxRate += !empty($tax->rate) ? $tax->rate : 0;
        }

        return $taxRate;
    }

    public static function userBalance($users, $id, $amount, $type)
    {
        if($users == 'customer')
        {
            $user = Customer::find($id);
        }
        else
        {
            $user = Vender::find($id);
        }

        if(!empty($user))
        {
            if($type == 'credit')
            {
                $oldBalance    = $user->balance;
                $user->balance = $oldBalance + $amount;
                $user->save();
            }
            elseif($type == 'debit')
            {
                $oldBalance    = $user->balance;
                $user->balance = $oldBalance - $amount;
                $user->save();
            }
        }
    }

    public static function bankAccountBalance($id, $amount, $type)
    {
        $bankAccount = BankAccount::find($id);
        if($bankAccount)
        {
            if($type == 'credit')
            {
                $oldBalance                   = $bankAccount->opening_balance;
                $bankAccount->opening_balance = $oldBalance + $amount;
                $bankAccount->save();
            }
            elseif($type == 'debit')
            {
                $oldBalance                   = $bankAccount->opening_balance;
                $bankAccount->opening_balance = $oldBalance - $amount;
                $bankAccount->save();
            }
        }

    }

    // get font-color code accourding to bg-color
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3)
        {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        }
        else
        {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R   = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for($i = 0; $i < count($C); ++$i)
        {
            if($C[$i] <= 0.03928)
            {
                $C[$i] = $C[$i] / 12.92;
            }
            else
            {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if($L > 0.179)
        {
            $color = 'black';
        }
        else
        {
            $color = 'white';
        }

        return $color;
    }

    public static function delete_directory($dir)
    {
        if(!file_exists($dir))
        {
            return true;
        }
        if(!is_dir($dir))
        {
            return unlink($dir);
        }
        foreach(scandir($dir) as $item)
        {
            if($item == '.' || $item == '..')
            {
                continue;
            }
            if(!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item))
            {
                return false;
            }
        }

        return rmdir($dir);
    }

    public static $chartOfAccountType = [
        'assets' => 'Assets',
        'liabilities' => 'Liabilities',
        'expenses' => 'Expenses',
        'income' => 'Income',
        'equity' => 'Equity',
    ];


    public static $chartOfAccountSubType = array(
        "assets" => array(
            '1' => 'Current Asset',
            '2' => 'Fixed Asset',
            '3' => 'Inventory',
            '4' => 'Non-current Asset',
            '5' => 'Prepayment',
            '6' => 'Bank & Cash',
            '7' => 'Depreciation',
        ),
        "liabilities" => array(
            '1' => 'Current Liability',
            '2' => 'Liability',
            '3' => 'Non-current Liability',
        ),
        "expenses" => array(
            '1' => 'Direct Costs',
            '2' => 'Expense',
        ),
        "income" => array(
            '1' => 'Revenue',
            '2' => 'Sales',
            '3' => 'Other Income',
        ),
        "equity" => array(
            '1' => 'Equity',
        ),

    );

    public static function chartOfAccountTypeData()
    {
        $chartOfAccountTypes = Self::$chartOfAccountType;
        foreach($chartOfAccountTypes as $k => $type)
        {

            $accountType = ChartOfAccountType::create(
                [
                    'name' => $type,
                    'created_by' => 1,
                ]
            );

            $chartOfAccountSubTypes = Self::$chartOfAccountSubTypeNew;

            foreach($chartOfAccountSubTypes[$k] as $subType)
            {
                ChartOfAccountSubType::create(
                    [
                        'name' => $subType,
                        'type' => $accountType->id,
                    ]
                );
            }
        }
    }

    public static $chartOfAccount = array(

        [
            'code' => '120',
            'name' => 'Accounts Receivable',
            'type' => 1,
            'sub_type' => 1,
        ],
        [
            'code' => '160',
            'name' => 'Computer Equipment',
            'type' => 1,
            'sub_type' => 2,
        ],
        [
            'code' => '150',
            'name' => 'Office Equipment',
            'type' => 1,
            'sub_type' => 2,
        ],
        [
            'code' => '140',
            'name' => 'Inventory',
            'type' => 1,
            'sub_type' => 3,
        ],
        [
            'code' => '857',
            'name' => 'Budget - Finance Staff',
            'type' => 1,
            'sub_type' => 6,
        ],
        [
            'code' => '170',
            'name' => 'Accumulated Depreciation',
            'type' => 1,
            'sub_type' => 7,
        ],
        [
            'code' => '200',
            'name' => 'Accounts Payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '205',
            'name' => 'Accruals',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '150',
            'name' => 'Office Equipment',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '855',
            'name' => 'Clearing Account',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '235',
            'name' => 'Employee Benefits Payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '236',
            'name' => 'Employee Deductions payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '255',
            'name' => 'Historical Adjustments',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '835',
            'name' => 'Revenue Received in Advance',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '260',
            'name' => 'Rounding',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '500',
            'name' => 'Costs of Goods Sold',
            'type' => 3,
            'sub_type' => 11,
        ],
        [
            'code' => '600',
            'name' => 'Advertising',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '644',
            'name' => 'Automobile Expenses',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '684',
            'name' => 'Bad Debts',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '810',
            'name' => 'Bank Revaluations',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '605',
            'name' => 'Bank Service Charges',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '615',
            'name' => 'Consulting & Accounting',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '700',
            'name' => 'Depreciation',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '628',
            'name' => 'General Expenses',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '460',
            'name' => 'Interest Income',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '470',
            'name' => 'Other Revenue',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '475',
            'name' => 'Purchase Discount',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '400',
            'name' => 'Sales',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '330',
            'name' => 'Common Stock',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '300',
            'name' => 'Owners Contribution',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '310',
            'name' => 'Owners Draw',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '320',
            'name' => 'Retained Earnings',
            'type' => 5,
            'sub_type' => 16,
        ],
    );


    public static $chartOfAccountSubTypeNew = array(
        "assets" => array(
            '1' => 'Current Asset',
            '2' => 'Fixed Asset',
            '3' => 'Inventory',
            '4' => 'Non-current Asset',
            '5' => 'Prepayment',
            '6' => 'Bank & Cash',
            '7' => 'Depreciation',
        ),
        "liabilities" => array(
            '1' => 'Current Liability',
            '2' => 'Liability',
            '3' => 'Non-current Liability',
        ),
        "expenses" => array(
            '1' => 'Direct Costs',
            '2' => 'Expense',
            '3' => 'Depreciation',
            '4' => 'Cost of Goods Sold (COGS)',
        ),
        "income" => array(
            '1' => 'Revenue',
            '2' => 'Sales',
            '3' => 'Other Income',
            '4' => 'Contra-Revenue (Reduces Income)',
        ),
        "equity" => array(
            '1' => 'Equity',
        ),

    );

    public static $chartOfAccountNew = array(


        [
            'code' => '1000',
            'name' => 'Cash on Hand',
            'type' => '1',
            'sub_type' => '6',
        ],
        [
            'code' => '1010',
            'name' => 'Bank Account - AED',
            'type' => '1',
            'sub_type' => '6',
        ],
        [
            'code' => '1020',
            'name' => 'Bank Account - USD',
            'type' => '1',
            'sub_type' => '6',
        ],
        [
            'code' => '1030',
            'name' => 'Accounts Receivable',
            'type' => '1',
            'sub_type' => '1',
        ],
        [
            'code' => '1040',
            'name' => 'Prepaid Expenses',
            'type' => '1',
            'sub_type' => '5',
        ],
        [
            'code' => '1050',
            'name' => 'Vehicles in Transit (Inventory)',
            'type' => '1',
            'sub_type' => '3',
        ],
        [
            'code' => '1060',
            'name' => 'Fuel & Maintenance Supplies',
            'type' => '1',
            'sub_type' => '3',
        ],
        [
            'code' => '1070',
            'name' => 'Deposits (Port/Customs)',
            'type' => '1',
            'sub_type' => '1',
        ],
        [
            'code' => '1080',
            'name' => 'Fixed Assets - Office Equipment',
            'type' => '1',
            'sub_type' => '2',
        ],
        [
            'code' => '1090',
            'name' => 'Fixed Assets - Vehicles',
            'type' => '1',
            'sub_type' => '2',
        ],
        [
            'code' => '1100',
            'name' => 'Business Bank Account',
            'type' => '1',
            'sub_type' => '6',
        ],
        [
            'code' => '2000',
            'name' => 'Accounts Payable',
            'type' => '2',
            'sub_type' => '8',
        ],
        [
            'code' => '2010',
            'name' => 'Loan Payable - Banks',
            'type' => '2',
            'sub_type' => '10',
        ],
        [
            'code' => '2020',
            'name' => 'Customs Duty Payable',
            'type' => '2',
            'sub_type' => '8',
        ],
        [
            'code' => '2030',
            'name' => 'VAT Payable',
            'type' => '2',
            'sub_type' => '8',
        ],
        [
            'code' => '3000',
            'name' => 'Owner’s Capital',
            'type' => '5',
            'sub_type' => '19',
        ],
        [
            'code' => '3010',
            'name' => 'Retained Earnings',
            'type' => '5',
            'sub_type' => '19',
        ],
        [
            'code' => '4000',
            'name' => 'Freight Income (Shipping Fees)',
            'type' => '4',
            'sub_type' => '15',
        ],
        [
            'code' => '4010',
            'name' => 'Vehicle Auction Pass-Through (Zero-profit)',
            'type' => '4',
            'sub_type' => '15',
        ],
        [
            'code' => '4020',
            'name' => 'Towing Revenue (Profit Center)',
            'type' => '4',
            'sub_type' => '15',
        ],
        [
            'code' => '4030',
            'name' => 'Shipping Revenue (Profit Center)',
            'type' => '4',
            'sub_type' => '15',
        ],
        [
            'code' => '4040',
            'name' => 'Sales Discount',
            'type' => '4',
            'sub_type' => '18',
        ],
        [
            'code' => '5000',
            'name' => 'Shipping & Freight Charges',
            'type' => '3',
            'sub_type' => '11',
        ],
        [
            'code' => '5040',
            'name' => 'Salaries & Wages',
            'type' => '3',
            'sub_type' => '11',
        ],
        [
            'code' => '5100',
            'name' => 'Depreciation - Vehicles',
            'type' => '3',
            'sub_type' => '13',
        ],
        [
            'code' => '5110',
            'name' => 'Vehicle Auction Cost (Pass-through)',
            'type' => '3',
            'sub_type' => '14',
        ],
        [
            'code' => '5120',
            'name' => 'Towing Costs (Driver Wages, Fuel, Maintenance)',
            'type' => '3',
            'sub_type' => '14',
        ],
        [
            'code' => '5130',
            'name' => 'Shipping Costs (Ocean Freight, Port Fees, Customs Fees)',
            'type' => '3',
            'sub_type' => '14',
        ],
    );

    public static function chartOfAccountData($user)
    {
        $chartOfAccounts = Self::$chartOfAccountNew;
        foreach($chartOfAccounts as $account)
        {
            ChartOfAccount::create(
                [
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'sub_type' => $account['sub_type'],
                    'is_enabled' => 1,
                    'created_by' => $user->id,
                ]
            );

        }
    }


    public static function getCompanyPaymentSetting()
    {

        $data     = \DB::table('company_payment_settings');
        $settings = [];

        if(\Auth::check())
        {
            $user_id = \Auth::user()->creatorId();
            $data    = $data->where('created_by', '=', $user_id);

        }
        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getCompanyPayment()
    {

        $data     = \DB::table('company_payment_settings');
        $settings = [];
        if(\Auth::check())
        {
            $user_id = \Auth::user()->creatorId();
            $data    = $data->where('created_by', '=', $user_id);

        }
        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getNonAuthCompanyPaymentSetting($id)
    {
        $data     = \DB::table('company_payment_settings');
        $settings = [];
        $data    = $data->where('created_by', '=', $id);

        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function error_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "error" : $msg;
        $msg_id    = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }

    public static function success_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "success" : $msg;
        $msg_id    = 'success.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }
    public static function invoice_payment_settings($id)
    {
        $data = [];

        $user = User::where(['id' => $id])->first();
        if(!is_null($user)){
            $data = DB::table('company_payment_settings');
            $data->where('created_by', '=',$id);
            $data = $data->get();
            //dd($data);
        }

        $res = [];

        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }


    public static function bill_payment_settings($id)
    {
        $data = [];

        $user = User::where(['id' => $id])->first();
        if(!is_null($user)){
            $data = DB::table('company_payment_settings');
            $data->where('created_by', '=',$id);
            $data = $data->get();
            //dd($data);
        }

        $res = [];

        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }
    public static function settingById($id){
        $data = DB::table('settings')->where('created_by', '=', $id)->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INVO",
            "journal_prefix" => "#JUR",
            "invoice_color" => "ffffff",
            "proposal_prefix" => "#PROP",
            "proposal_color" => "ffffff",
            "proposal_logo" => " ",
            "retainer_logo" => " ",
            "invoice_logo" => " ",
            "bill_logo" => " ",
            "retainer_color" => "ffffff",
            "bill_prefix" => "#BILL",
            "bill_color" => "ffffff",
            "customer_prefix" => "#CUST",
            "vender_prefix" => "#VEND",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "bill_template" => "template1",
            "proposal_template" => "template1",
            "retainer_template" => "template1",
            "registration_number" => "",
            "vat_number" => "",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "decimal_number" => "2",
            "tax_number" => "on",
            "tax_type" => "",
            "shipping_display" => "on",
            "journal_prefix" => "#JUR",
            "display_landing_page" => "on",
            "title_text" => "",
            'gdpr_cookie' => "off",
            'cookie_text' => "",
            "retainer_starting_number" => "1",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    //Twilio Notification
    public static function send_twilio_msg($to, $msg)
    {
        $settings  = Utility::settings(\Auth::user()->creatorId());
        $account_sid    = $settings['twilio_sid'];
        $auth_token = $settings['twilio_token'];
        $twilio_number = $settings['twilio_from'];

        try{
            $client        = new Client($account_sid, $auth_token);
            $client->messages->create($to, [
                'from' => $twilio_number,
                'body' => $msg,
            ]);
        }
        catch(\Exception $e){

        }
        //  dd('SMS Sent Successfully.');

    }


    //inventory management (Quantity)
    public static function total_quantity($type, $quantity, $product_id)
    {
        $product      = ProductService::find($product_id);
        $pro_quantity = $product->quantity;

        if($type == 'minus')
        {
            $product->quantity = $pro_quantity - $quantity;
        }
        else
        {
            $product->quantity = $pro_quantity + $quantity;
        }
        $product->save();
    }

    public static function starting_number($id,$type)
    {


        if($type == 'invoice'){
            $data=  DB::table('settings')
                      ->where('created_by', \Auth::user()->creatorId())
                      ->where('name', 'invoice_starting_number')
                      ->update(array('value' => $id));
        }
        elseif($type == 'proposal'){
            $data=  DB::table('settings')
                      ->where('created_by', \Auth::user()->creatorId())
                      ->where('name', 'proposal_starting_number')
                      ->update(array('value' => $id));
        }
        elseif($type == 'retainer')
        {
            $data = DB::table('settings')
                        ->where('created_by', \Auth::user()->creatorId())
                        ->where('name', 'retainer_starting_number')
                        ->update(array('value' => $id));
        }
        elseif($type == 'bill'){
            $data=  DB::table('settings')
                      ->where('created_by', \Auth::user()->creatorId())
                      ->where('name', 'bill_starting_number')
                      ->update(array('value' => $id));
        }


        return $data;
    }

    //add quantity in product stock
    public static function addProductStock($product_id, $quantity, $type, $description,$type_id)
    {

        $stocks             = new StockReport();
        $stocks->product_id = $product_id;
        $stocks->quantity	 = $quantity;
        $stocks->type = $type;
        $stocks->type_id = $type_id;
        $stocks->description = $description;
        $stocks->created_by =\Auth::user()->creatorId();
        $stocks->save();
    }

    public static function mode_layout()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data     = $data->get();
        $settings = [
            "cust_darklayout" => "off",
            "cust_theme_bg" => "on",
        ];
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function colorset(){
        if(\Auth::user())
        {
            $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value','name')->toArray();
        }

        if(!isset($setting['color']))
        {
            $setting = Utility::settings();
        }
        return $setting;
    }

    public static function get_superadmin_logo(){
        // $is_dark_mode = self::getValByName('cust_darklayout');
        // $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value','name')->toArray();
        // $is_dark_mode = isset($setting['cust_darklayout']) ? $setting['cust_darklayout'] : $is_dark_mode;

        // if($is_dark_mode == 'on')
        // {
        //     return 'logo-light.png';
        // }
        // else
        // {
        //     return 'logo-dark.png';
        // }


        $is_dark_mode = DB::table('settings')->where('created_by', '1')->pluck('value','name')->toArray();
        if(!empty($is_dark_mode['cust_darklayout'])){
            $is_dark_modes = $is_dark_mode['cust_darklayout'];

            if($is_dark_modes == 'on'){
                return 'logo-light.png';
            }else{
                return 'logo-dark.png';
            }
        }
        else{
            return 'logo-dark.png';
        }
    }

    public static function GetLogo()
    {
        $setting = Utility::colorset();
        if(\Auth::user() && \Auth::user()->type != 'super admin')
        {
            if($setting['cust_darklayout'] == 'on')
            {
                return Utility::getValByName('company_logo_light');
            }
            else
            {
                return Utility::getValByName('company_logo_dark');
            }
        }
        else
        {
            if($setting['cust_darklayout'] == 'on')
            {
                return Utility::getValByName('logo_light');
            }
            else
            {
                return Utility::getValByName('logo_dark');
            }
        }
    }

     // used for replace email variable (parameter 'template_name','id(get particular record by id for data)')
     public static function replaceVariable($content, $obj)
     {
         $arrVariable = [
             '{payment_name}',
             '{payment_bill}',
             '{payment_amount}',
             '{payment_date}',
             '{payment_method}',
             '{invoice_name}',
             '{invoice_number}',
             '{invoice_url}',
             '{bill_name}',
             '{bill_number}',
             '{bill_url}',
             '{payment_dueAmount}',
             '{proposal_name}',
             '{proposal_number}',
             '{proposal_url}',
             '{app_name}',
             '{company_name}',
             '{app_url}',
             '{email}',
             '{password}',
             '{contract_customer}',
             '{contract_subject}',
             '{contract_start_date}',
             '{contract_end_date}',
             '{contract_type}',
             '{contract_value}',
             '{retainer_name}',
             '{retainer_number}',
             '{retainer_url}',
         ];
         $arrValue    = [
             'payment_name' => '-',
             'payment_bill' => '-',
             'payment_amount' => '-',
             'payment_date' => '-',
             'payment_method' => '-',
             'invoice_name' => '-',
             'invoice_number' => '-',
             'invoice_url' => '-',
             'bill_name' => '-',
             'bill_number' => '-',
             'bill_url' => '-',
             'payment_dueAmount' => '-',
             'proposal_name' => '-',
             'proposal_number' => '-',
             'proposal_url' => '-',
             'app_name' => '-',
             'company_name' => '-',
             'app_url' => '-',
             'email' => '-',
             'password' => '-',
             'contract_customer' => '-',
             'contract_subject' => '-',
             'contract_start_date' => '-',
             'contract_end_date' => '-',
             'contract_type' => '-',
             'contract_value' => '-',
             'retainer_name' => '-',
             'retainer_number' => '-',
             'retainer_url' => '-',

         ];

         foreach($obj as $key => $val)
         {
             $arrValue[$key] = $val;
         }

         $settings = Utility::settings();
         $company_name = $settings['company_name'];

         $arrValue['app_name']     =  env('APP_NAME');
         $arrValue['company_name'] = self::settings()['company_name'];
         $arrValue['app_url']      = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

         return str_replace($arrVariable, array_values($arrValue), $content);
     }

     // Email Template Modules Function START
     // Common Function That used to send mail with check all cases
     public static function sendEmailTemplate($emailTemplate, $mailTo, $obj)
     {
         $usr = \Auth::user();

         //Remove Current Login user Email don't send mail to them
         unset($mailTo[$usr->id]);
        //  dd($mailTo);
         $mailTo = array_values($mailTo);

        if($usr->type != 'super admin')
        {

            // find template is exist or not in our record
            $template = EmailTemplate::where('slug', $emailTemplate)->first();

            if(isset($template) && !empty($template))
            {
                // check template is active or not by company
                $is_active = UserEmailTemplate::where('template_id', '=', $template->id)->where('user_id', '=', $usr->creatorId())->first();

                if($is_active->is_active == 1)
                {
                    $settings = self::settings();

                    // get email content language base
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();

                    $content->from = $template->from;

                    if(!empty($content->content))
                    {

                        $content->content = self::replaceVariable($content->content, $obj);
                        // send email
                    //  dd($mailTo);
                        try
                        {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));

                        }
                        catch(\Exception $e)
                        {

                            $error = __('E-Mail has been not sent due to SMTP configuration');
                        }

                        if(isset($error))
                        {
                            $arReturn = [
                                'is_success' => false,
                                'error' => $error,
                            ];
                        }
                        else
                        {
                            $arReturn = [
                                'is_success' => true,
                                'error' => false,
                            ];
                        }
                    }
                    else
                    {
                        $arReturn = [
                            'is_success' => false,
                            'error' => __('Mail not send, email is empty'),
                        ];
                    }

                    return $arReturn;
                }
                else
                {
                    return [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            }
            else
            {
                return [
                    'is_success' => false,
                    'error' => __('Mail not send, email not found'),
                ];
            }
        }
     }

    // Make Entry in email_tempalte_lang table when create new language
    public static function makeEmailLang($lang)
    {
        $template = EmailTemplate::all();
        foreach($template as $t)
        {
            $default_lang                 = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang            = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang      = $lang;
            $emailTemplateLang->subject   = $default_lang->subject;
            $emailTemplateLang->content   = $default_lang->content;
            $emailTemplateLang->save();
        }
    }

      // Email Template Modules Function END


      public static function upload_file($request,$key_name,$name,$path,$custom_validation =[]){
        try{
            $settings = Utility::getStorageSetting();
        //    dd($settings);

            if(!empty($settings['storage_setting'])){

                if($settings['storage_setting'] == 'wasabi'){

                    config(
                        [
                            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.endpoint' => 'https://s3.'.$settings['wasabi_region'].'.wasabisys.com'
                        ]
                    );

                    $max_size = !empty($settings['wasabi_max_upload_size'])? $settings['wasabi_max_upload_size']:'2048';
                    $mimes =  !empty($settings['wasabi_storage_validation'])? $settings['wasabi_storage_validation']:'';

                }else if($settings['storage_setting'] == 's3'){
                    config(
                        [
                            'filesystems.disks.s3.key' => $settings['s3_key'],
                            'filesystems.disks.s3.secret' => $settings['s3_secret'],
                            'filesystems.disks.s3.region' => $settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                            'filesystems.disks.s3.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings['s3_max_upload_size'])? $settings['s3_max_upload_size']:'2048';
                    $mimes =  !empty($settings['s3_storage_validation'])? $settings['s3_storage_validation']:'';


                }else{
                    $max_size = !empty($settings['local_storage_max_upload_size'])? $settings['local_storage_max_upload_size']:'2048';

                    $mimes =  !empty($settings['local_storage_validation'])? $settings['local_storage_validation']:'';
                }


                $file = $request->$key_name;


                if(count($custom_validation) > 0){
                    $validation =$custom_validation;
                }else{

                    $validation =[
                        'mimes:'.$mimes,
                        'max:'.$max_size,
                    ];

                }
                $validator = \Validator::make($request->all(), [
                    $key_name =>$validation
                ]);

                if($validator->fails()){
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    if($settings['storage_setting']=='local')
                        {
                            $request->$key_name->move(storage_path($path), $name);
                            $path = $path.$name;
                        }
                    else if($settings['storage_setting'] == 'wasabi'){

                        $path = \Storage::disk('wasabi')->putFileAs(
                            $path,
                            $file,
                            $name
                        );

                        // $path = $path.$name;

                    }else if($settings['storage_setting'] == 's3'){

                        $path = \Storage::disk('s3')->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                        // $path = $path.$name;
                        // dd($path);
                    }


                    $res = [
                        'flag' => 1,
                        'msg'  =>'success',
                        'url'  => $path
                    ];
                    return $res;
                }

            }else{
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }

        }catch(\Exception $e){
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }


    public static function get_file($path){
        $settings = Utility::getStorageSetting();

        try {
            if($settings['storage_setting'] == 'wasabi'){
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.'.$settings['wasabi_region'].'.wasabisys.com'
                    ]
                );
            }elseif($settings['storage_setting'] == 's3'){
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['s3_key'],
                        'filesystems.disks.s3.secret' => $settings['s3_secret'],
                        'filesystems.disks.s3.region' => $settings['s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_setting'])->url($path);
        } catch (\Throwable $th) {
            return '';
        }
    }


    public static function get_company_logo()
    {
        $is_dark_mode = self::getValByName('cust_darklayout');
        if($is_dark_mode == 'on'){
            $logo = self::getValByName('cust_darklayout');
            return Utility::getValByName('company_logo_light');
        }else{
            return Utility::getValByName('company_logo_dark');
        }
    }


}
