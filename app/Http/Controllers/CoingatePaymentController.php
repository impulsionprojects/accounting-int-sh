<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Retainer;
use App\Models\RetainerPayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use CoinGate\CoinGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CoingatePaymentController extends Controller
{
    //


    public $mode;
    public $coingate_auth_token;
    public $is_enabled;

    public function paymentConfig()
    {
        if (\Auth::user()->type == 'company') {
            $payment_setting = Utility::getCompanyPaymentSetting();;
        } else {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }

        $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
        $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
        $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';

        return $this;
    }




    public function invoicePayWithCoingate(Request $request)
    {

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice   = Invoice::find($invoiceID);


        if ($invoice) {
            // dd($invoice);
            if (Auth::check()) {
                $payment   = $this->paymentConfig();
                $settings  = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            } else {
                $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
                $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
                $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
                $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';
                $settings = Utility::settingsById($invoice->created_by);
            }
            $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
            $result    = array();
            $price = $request->amount;
            if ($price > 0) {
                CoinGate::config(
                    array(
                        'environment' => $this->mode,
                        'auth_token' => $this->coingate_auth_token,
                        'curlopt_ssl_verifypeer' => FALSE,
                    )
                );
                $post_params = array(
                    'order_id' => time(),
                    'price_amount' => $price,
                    'price_currency' => Utility::getValByName('site_currency'),
                    'receive_currency' => Utility::getValByName('site_currency'),
                    'callback_url' => route(
                        'customer.invoice.coingate',
                        [
                            $request->invoice_id,
                            $price,
                        ]
                    ),
                    'cancel_url' => route('invoice.show', [Crypt::encrypt($request->invoice_id)]),
                    'success_url' => route(
                        'customer.invoice.coingate',
                        [
                            $request->invoice_id,
                            $price,
                        ]
                    ),
                    'title' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                );

                $order = \CoinGate\Merchant\Order::create($post_params);
                if ($order) {
                    return redirect($order->payment_url);
                } else {
                    return redirect()->back()->with('error', __('opps something wren wrong.'));
                }
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return redirect()->route('invoice.index')->with('error', __('Invoice is deleted.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        if (Auth::check()) {
            $payment   = $this->paymentConfig();
            $settings  = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
        } else {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
            $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
            $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
            $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';
            $settings = Utility::settingsById($invoice->created_by);
        }
        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
        $result    = array();

        if ($invoice) {
            $payments = InvoicePayment::create(
                [
                    'invoice_id' => $invoice->id,
                    'date' => date('Y-m-d'),
                    'amount' => $amount,
                    'payment_method' => 1,
                    'order_id' => $orderID,
                    'payment_type' => __('Coingate'),
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
            if (Auth::check()){
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

    public function retainerPayWithCoingate(Request $request)
    {

        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($request->retainer_id);
        $retainer   = Retainer::find($retainerID);


        if ($retainer) {
            // dd($invoice);
            if (Auth::check()) {
                $payment   = $this->paymentConfig();
                $settings  = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            } else {
                $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
                $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
                $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
                $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';
                $settings = Utility::settingsById($retainer->created_by);
            }
            $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
            $result    = array();
            $price = $request->amount;
            if ($price > 0) {
                CoinGate::config(
                    array(
                        'environment' => $this->mode,
                        'auth_token' => $this->coingate_auth_token,
                        'curlopt_ssl_verifypeer' => FALSE,
                    )
                );
                $post_params = array(
                    'order_id' => time(),
                    'price_amount' => $price,
                    'price_currency' => Utility::getValByName('site_currency'),
                    'receive_currency' => Utility::getValByName('site_currency'),
                    'callback_url' => route(
                        'customer.retainer.coingate',
                        [
                            $request->retainer_id,
                            $price,
                        ]
                    ),
                    'cancel_url' => route('customer.retainer.show', [Crypt::encrypt($request->retainer_id)]),
                    'success_url' => route(
                        'customer.retainer.coingate',
                        [
                            $request->retainer_id,
                            $price,
                        ]
                    ),
                    'title' => __('Retainer') . ' ' . Utility::retainerNumberFormat($settings, $retainer->retainer_id),
                );

                $order = \CoinGate\Merchant\Order::create($post_params);
                if ($order) {
                    return redirect($order->payment_url);
                } else {
                    return redirect()->back()->with('error', __('opps something wren wrong.'));
                }
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return redirect()->route('customer.retainer')->with('error', __('Invoice is deleted.'));
        }
    }

    public function getRetainerPaymentStatus(Request $request, $retainer_id, $amount)
    {
        
        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($retainer_id);
        $retainer   = Retainer::find($retainerID);
        if (Auth::check()) {
            $payment   = $this->paymentConfig();
            $settings  = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
        } else {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
            $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
            $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
            $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';
            $settings = Utility::settingsById($retainer->created_by);
        }
        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
        $result    = array();

        if ($retainer) {
            $payments = RetainerPayment::create(
                [
                    'retainer_id' => $retainer->id,
                    'date' => date('Y-m-d'),
                    'amount' => $amount,
                    'payment_method' => 1,
                    'order_id' => $orderID,
                    'payment_type' => __('Coingate'),
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
            // $setting  = Utility::settings(\Auth::user()->creatorId());
            // $customer = Customer::find($retainer->customer_id);
            // if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
            // {
            //     $msg = __("New payment of").' ' . $amount . __("created for").' ' . $customer->name . __("by").' '.  $payments['payment_type'] . '.';
            //     Utility::send_twilio_msg($customer->contact,$msg);
            // }


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
}
