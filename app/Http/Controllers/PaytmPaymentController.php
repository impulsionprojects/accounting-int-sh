<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Retainer;
use App\Models\RetainerPayment;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PaytmWallet;

class PaytmPaymentController extends Controller
{
    public function paymentConfig()
    {
        $payment_setting = Utility::getCompanyPaymentSetting();

        config(
            [
                'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                'services.paytm-wallet.channel' => 'WEB',
                'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
            ]
        );
    }


    public function invoicePayWithPaytm(Request $request)
    {

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice   = Invoice::find($invoiceID);

        if ($invoice) {
            if (Auth::check()) {
                $payment  = $this->paymentConfig();
                $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            } else {
                $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
                $settings = Utility::settingsById($invoice->created_by);
                config(
                    [
                        'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                        'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                        'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                        'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                        'services.paytm-wallet.channel' => 'WEB',
                        'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
                    ]
                );
            }

            $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));

            $price = $request->amount;
            if ($price > 0) {
                $call_back = route(
                    'customer.invoice.paytm',
                    [
                        $request->invoice_id,
                        $price
                    ]
                );
                $payment   = PaytmWallet::with('receive');

                $payment->prepare(
                    [
                        'order' => date('Y-m-d') . '-' . strtotime(date('Y-m-d H:i:s')),
                        'user' => $invoice->customer->id,
                        'mobile_number' => $request->mobile,
                        'email' => $invoice->customer->email,
                        'amount' => $price,
                        'invoice' => $invoice->id,
                        'callback_url' => $call_back,
                    ]
                );


                return $payment->receive();
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return Utility::error_res(__('Invoice is deleted.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        if (Auth::check()) {
            $payment  = $this->paymentConfig();
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
        } else {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
            $settings = Utility::settingsById($invoice->created_by);
            config(
                [
                    'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                    'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                    'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                    'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                    'services.paytm-wallet.channel' => 'WEB',
                    'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
                ]
            );
        }


        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($invoice) {
            try {
                $transaction = PaytmWallet::with('receive');

                $response    = $transaction->response();

                if ($transaction->isSuccessful()) {

                    $payments = InvoicePayment::create(
                        [
                            'invoice_id' => $invoice->id,
                            'date' => date('Y-m-d'),
                            'amount' => $amount,
                            'payment_method' => 1,
                            'order_id' => $orderID,
                            'payment_type' => __('Paytm'),
                            'receipt' => '',
                            'description' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                        ]
                    );

                    $invoice = Invoice::find($invoice->id);

                    if ($invoice->getDue() <= 0.0) {
                        Invoice::change_status($invoice->id, 4);
                    } elseif ($invoice->getDue() > 0) {
                        Invoice::change_status($invoice->id, 3);
                    } else {
                        Invoice::change_status($invoice->id, 2);
                    }

                    //Twilio Notification
                    if (Auth::check()) {
                        $setting  = Utility::settings(\Auth::user()->creatorId());
                        }
                        $customer = Customer::find($invoice->customer_id);
                    if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
                    {
                        $msg = __("New payment of").' ' . $amount . __("created for").' ' . $customer->name . __("by").' '.  $payments['payment_type'] . '.';
                        Utility::send_twilio_msg($customer->contact,$msg);
                    }

                    if (Auth::check()) {
                        return redirect()->route('invoice.show', \Crypt::encrypt($invoice->id))->with('success', __('Payment successfully added.'));
                    } else {
                        return redirect()->back()->with('success', __(' Payment successfully added.'));
                    }
                }
                else
                {
                    if (Auth::check()) {
                        return redirect()->route('invoice.show', \Crypt::encrypt($invoice->id))->with('error', __('Transaction has been ' . $status));
                    } else {
                        return redirect()->back()->with('success', __('Transaction succesfull'));
                    }
                }
            }
            catch(\Exception $e)
            {
                return redirect()->back()->with('error', __('Invoice not found!'));
            }

        }
    }

    public function retainerPayWithPaytm(Request $request)
    {

        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($request->retainer_id);
        $retainer   = Retainer::find($retainerID);

        if ($retainer) {

            if (Auth::check()) {
                $payment  = $this->paymentConfig();
                $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            } else {
                $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
                $settings = Utility::settingsById($retainer->created_by);
                config(
                    [
                        'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                        'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                        'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                        'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                        'services.paytm-wallet.channel' => 'WEB',
                        'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
                    ]
                );
            }

            $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));

            $price = $request->amount;
            if ($price > 0) {
                $call_back = route(
                    'customer.retainer.paytm',
                    [
                        $request->retainer_id,
                        $price
                    ]
                );

                $payment   = PaytmWallet::with('receive');

                $payment->prepare(
                    [
                        'order' => date('Y-m-d') . '-' . strtotime(date('Y-m-d H:i:s')),
                        'user' => $retainer->customer->id,
                        'mobile_number' => $request->mobile,
                        'email' => $retainer->customer->email,
                        'amount' => $price,
                        'Retainer' => $retainer->id,
                        'callback_url' => $call_back,
                    ]
                );


                return $payment->receive();
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return Utility::error_res(__('Invoice is deleted.'));
        }
    }

    public function getRetainerPaymentStatus(Request $request, $retainer_id, $amount)
    {
      
        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($retainer_id);
        $retainer   = Retainer::find($retainerID);

        if (Auth::check()) {
            $payment  = $this->paymentConfig();
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
        } else {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
            $settings = Utility::settingsById($retainer->created_by);
            config(
                [
                    'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                    'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                    'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                    'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                    'services.paytm-wallet.channel' => 'WEB',
                    'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
                ]
            );
        }

        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($retainer) {
            try
            {
            $transaction = PaytmWallet::with('receive');

            $response    = $transaction->response();

            if ($transaction->isSuccessful()) {

                $payments = RetainerPayment::create(
                    [
                        'retainer_id' => $retainer->id,
                        'date' => date('Y-m-d'),
                        'amount' => $amount,
                        'payment_method' => 1,
                        'order_id' => $orderID,
                        'payment_type' => __('Paytm'),
                        'receipt' => '',
                        'description' => __('Retainer') . ' ' . Utility::retainerNumberFormat($settings, $retainer->retainer_id),
                    ]
                );

                $retainer = Retainer::find($retainer->id);

                if ($retainer->getDue() <= 0.0) {
                    Retainer::change_status($retainer->id, 4);
                } elseif ($retainer->getDue() > 0) {
                    Retainer::change_status($retainer->id, 3);
                } else {
                    Retainer::change_status($retainer->id, 2);
                }

                //Twilio Notification
                if (Auth::check()) {
                $setting  = Utility::settings(\Auth::user()->creatorId());
                }
                $customer = Customer::find($retainer->customer_id);
                if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
                {
                    $msg = __("New payment of").' ' . $amount . __("created for").' ' . $customer->name . __("by").' '.  $payments['payment_type'] . '.';
                    Utility::send_twilio_msg($customer->contact,$msg);
                }
                if (Auth::check()) {
                    return redirect()->route('customer.retainer.show', \Crypt::encrypt($retainer->id))->with('success', __('Payment successfully added.'));
                } else {
                    return redirect()->back()->with('success', __(' Payment successfully added.'));
                }
                }
                else
                {
                    if (Auth::check()) {
                        return redirect()->route('customer.retainer.show', \Crypt::encrypt($retainer->id))->with('error', __('Transaction has been ' . $status));
                    } else {
                        return redirect()->back()->with('success', __('Transaction succesfull'));
                    }
                }
            }
            catch(\Exception $e)
            {
                if (Auth::check()) {
                    return redirect()->route('customer.retainer.show', \Crypt::encrypt($retainer->id))->with('error', __('Transaction has been failed.'));
                } else {
                    return redirect()->back()->with('success', __('Transaction has been complted.'));
                }
            }
        }
    }
}
