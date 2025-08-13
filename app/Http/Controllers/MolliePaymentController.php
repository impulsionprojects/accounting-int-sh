<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Retainer;
use App\Models\RetainerPayment;
use App\Models\Utility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MolliePaymentController extends Controller
{

    public $api_key;
    public $profile_id;
    public $partner_id;
    public $is_enabled;

    public function paymentConfig()
    {
        $payment_setting = Utility::getCompanyPaymentSetting();

        $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
        $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
        $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
        $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';

        return $this;
    }


    public function invoicePayWithMollie(Request $request)
    {
        $payment   = $this->paymentConfig();
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice   = Invoice::find($invoiceID);

        if ($invoice) {
            $price = $request->amount;

            if ($price > 0) {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                $payment = $mollie->payments->create(
                    [
                        "amount" => [
                            "currency" => Utility::getValByName('site_currency'),
                            "value" => number_format($price, 2),
                        ],
                        "description" => "payment for product",
                        "redirectUrl" => route(
                            'customer.invoice.mollie',
                            [
                                $request->invoice_id,
                                $price,
                            ]
                        ),
                    ]
                );

                session()->put('mollie_payment_id', $payment->id);

                return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return redirect()->back()->with('error', 'Invoice is deleted.');
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);

        $user = User::where('id', $invoice->created_by)->first();

        $payment = $this->paymentConfig();

        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');


        $result    = array();

        if ($invoice) {
            try {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if (session()->has('mollie_payment_id')) {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));

                    if ($payment->isPaid()) {


                        $payments = InvoicePayment::create(
                            [
                                'invoice_id' => $invoice->id,
                                'date' => date('Y-m-d'),
                                'amount' => $amount,
                                'payment_method' => 1,
                                'order_id' => $orderID,
                                'payment_type' => __('Mollie'),
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
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->back()->with('error', __('Invoice not found!'));
            }

        }
    }

    public function retainerPayWithMollie(Request $request)
    {

        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($request->retainer_id);
        $retainer   = Retainer::find($retainerID);

        if ($retainer) {
            $price = $request->amount;
            if ($price > 0) {
                if (Auth::check()) {
                    $payment = $this->paymentConfig();
                    $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
                } else {
                    $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
                    $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
                    $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
                    $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
                    $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';
                    $settings = Utility::settingsById($retainer->created_by);
                }
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                $payment = $mollie->payments->create(
                    [
                        "amount" => [
                            "currency" => Utility::getValByName('site_currency'),
                            "value" => number_format($price, 2),
                        ],
                        "description" => "payment for product",
                        "redirectUrl" => route(
                            'customer.retainer.mollie',
                            [
                                $request->retainer_id,
                                $price,
                            ]
                        ),
                    ]
                );

                session()->put('mollie_payment_id', $payment->id);

                return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
            } else {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }
        } else {
            return redirect()->back()->with('error', 'Invoice is deleted.');
        }
    }

    public function getRetainerPaymentStatus(Request $request, $retainer_id, $amount)
    {
        
        $retainerID = \Illuminate\Support\Facades\Crypt::decrypt($retainer_id);
        $retainer   = Retainer::find($retainerID);
        if (Auth::check()) {
            $payment = $this->paymentConfig();
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
        } else {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($retainer->created_by);
            $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
            $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
            $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
            $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';
            $settings = Utility::settingsById($retainer->created_by);
        }


        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));

        $result    = array();
        if ($retainer) {
            try {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if (session()->has('mollie_payment_id')) {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));

                    if ($payment->isPaid()) {


                        $payments = RetainerPayment::create(
                            [
                                'retainer_id' => $retainer->id,
                                'date' => date('Y-m-d'),
                                'amount' => $amount,
                                'payment_method' => 1,
                                'order_id' => $orderID,
                                'payment_type' => __('Mollie'),
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
                        if (Auth::check()){
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
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
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
