<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\InvoicePayment;
use App\Models\RetainerPayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    protected $invoiceData;


    public function paymentConfig()
    {
        if (\Auth::check()) {
            $payment_setting = Utility::getAdminPaymentSetting();
        } else {
            $payment_setting = Utility::getCompanyPaymentSetting($this->invoiceData);
        }
        // dd($payment_setting);

        if($payment_setting['paypal_mode'] == 'live'){
            config([
                      'paypal.live.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                      'paypal.live.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                      'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
                  ]);
        }else{
           config([
                      'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                      'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                      'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
                  ]);
        }

    }

    public function customerPayWithPaypal(Request $request, $invoice_id)
    {
        $this->paymentConfig();
        $invoice = Invoice::find($invoice_id);
        if (Auth::check()) {
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            $user     = \Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
            $settings = Utility::settingById($invoice->created_by);
        }


        $get_amount = $request->amount;

        $request->validate(['amount' => 'required|numeric|min:0']);

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        if ($invoice) {
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {


                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $name = Utility::invoiceNumberFormat($settings, $invoice->invoice_id);



                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('customer.get.payment.status',[$invoice->id,$get_amount]),
                        "cancel_url" =>  route('customer.get.payment.status',[$invoice->id,$get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => Utility::getValByName('site_currency'),
                                "value" => $get_amount
                            ]
                        ]
                    ]
                ]);


                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('invoice.show', \Crypt::encrypt($invoice->id))
                        ->with('error', 'Something went wrong.');
                } else {
                    return redirect()
                        ->route('invoice.show', \Crypt::encrypt($invoice->id))
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }


                return redirect()->route('customer.invoice.show',\Crypt::encrypt($invoice_id))->back()->with('error', __('Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

  

    public function customerretainerPayWithPaypal(Request $request, $retainer_id)
    {
        $this->paymentConfig();

        $retainer = Retainer::find($retainer_id);
        if (Auth::check()) {
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            $user     = \Auth::user();
        } else {
            $user = User::where('id', $retainer->created_by)->first();
            $settings = Utility::settingById($retainer->created_by);
        }


        $get_amount = $request->amount;

        $request->validate(['amount' => 'required|numeric|min:0']);
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        if ($retainer) {
            if ($get_amount > $retainer->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {


                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $name = Utility::invoiceNumberFormat($settings, $retainer->retainer_id);



                $paypalToken = $provider->getAccessToken();

                
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('customer.get.retainer.payment.status',[$retainer->id,$get_amount]),
                        "cancel_url" =>  route('customer.get.retainer.payment.status',[$retainer->id,$get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => Utility::getValByName('site_currency'),
                                "value" => $get_amount
                            ]
                        ]
                    ]
                ]);

                
                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('retainer.show', \Crypt::encrypt($retainer->id))
                        ->with('error', 'Something went wrong.');
                } else {
                    return redirect()
                        ->route('retainer.show', \Crypt::encrypt($retainer->id))
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }

                


                return redirect()->route('customer.retainer.show',\Crypt::encrypt($retainer_id))->back()->with('error', __('Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customerGetRetainerPaymentStatus(Request $request, $retainer_id , $amount)
    {
        // dd($request->all(), $retainer_id , $amount);
        $retainer = Retainer::find($retainer_id);
        if (Auth::check()) {
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            $user     = \Auth::user();
            // $this->setApiContext();
        } else {
            $user = User::where('id', $retainer->created_by)->first();
            $settings = Utility::settingById($retainer->created_by);
            // $this->non_auth_setApiContext($retainer->created_by);
        }



        $payment_id = Session::get('paypal_payment_id');

        Session::forget('paypal_payment_id');

        if (empty($request->PayerID || empty($request->token))) {
            return redirect()->back()->with('error', __('Payment failed'));
        }

            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
        


                $payments = RetainerPayment::create(
                    [

                        'retainer_id' => $retainer->id,
                        'date' => date('Y-m-d'),
                        'amount' => $amount,
                        'account_id' => 0,
                        'payment_method' => 0,
                        'order_id' => $order_id,
                        'currency' => Utility::getValByName('site_currency'),
                        'txn_id' => $payment_id,
                        'payment_type' => __('PAYPAL'),
                        'receipt' => '',
                        'reference' => '',
                        'description' => 'Retainer ' . Utility::retainerNumberFormat($settings, $retainer->retainer_id),
                    ]
                );

                if ($retainer->getDue() <= 0) {
                    $retainer->status = 4;
                    $retainer->save();
                } elseif (($retainer->getDue() - $payments->amount) == 0) {
                    $retainer->status = 4;
                    $retainer->save();
                } else {
                    $retainer->status = 3;
                    $retainer->save();
                }

                $retainerPayment              = new \App\Models\Transaction();
                $retainerPayment->user_id     = $retainer->customer_id;
                $retainerPayment->user_type   = 'Customer';
                $retainerPayment->type        = 'PAYPAL';
                $retainerPayment->created_by  = \Auth::check() ? \Auth::user()->id : $retainer->customer_id;
                $retainerPayment->payment_id  = $retainerPayment->id;
                $retainerPayment->category    = 'Retainer';
                $retainerPayment->amount      = $amount;
                $retainerPayment->date        = date('Y-m-d');
                $retainerPayment->created_by  = \Auth::check() ? \Auth::user()->creatorId() : $retainer->created_by;
                $retainerPayment->payment_id  = $payments->id;
                $retainerPayment->description = 'Retainer ' . Utility::retainerNumberFormat($settings, $retainer->retainer_id);
                $retainerPayment->account     = 0;

                \App\Models\Transaction::addTransaction($retainerPayment);
               
            

                if (Auth::check()) {
                    return redirect()->route('customer.retainer.show', \Crypt::encrypt($retainer->id))->with('success', __('Payment successfully added.'));
                } else {
                    return redirect()->back()->with('success', __(' Payment successfully added.'));
                }
        
    }

    public function customerGetPaymentStatus(Request $request, $invoice_id,$amount)
    {
        $this->paymentConfig();
        //         dd($request->all());
            $invoice = Invoice::find($invoice_id);

        if (Auth::check()) {
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            $user     = \Auth::user();
        //            $this->setApiContext();
                } else {
                    $user = User::where('id', $invoice->created_by)->first();
                    $settings = Utility::settingById($invoice->created_by);
                //            $this->non_auth_setApiContext($invoice->created_by);
                }



                $payment_id = Session::get('PayerID');
                $provider = new PayPalClient;
                $response = $provider->showAuthorizedPaymentDetails($request->PayerID);

                Session::forget('PayerID');

                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->back()->with('error', __('Payment failed'));
                }

       

                try {

                    $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
      
                $payments = InvoicePayment::create(
                    [

                        'invoice_id' => $invoice->id,
                        'date' => date('Y-m-d'),
                        'amount' => $amount,
                        'account_id' => 0,
                        'payment_method' => 0,
                        'order_id' => $order_id,
                        'currency' => Utility::getValByName('site_currency'),
                        'txn_id' => $payment_id,
                        'payment_type' => __('PAYPAL'),
                        'receipt' => '',
                        'reference' => '',
                        'description' => 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                    ]
                );

                if ($invoice->getDue() <= 0) {
                    $invoice->status = 4;
                    $invoice->save();
                } elseif (($invoice->getDue() - $payments->amount) == 0) {
                    $invoice->status = 4;
                    $invoice->save();
                } elseif ($invoice->getDue() > 0) {
                    $invoice->status = 3;
                    $invoice->save();
                }
                else {
                    $invoice->status = 2;
                    $invoice->save();
                }

                $invoicePayment              = new \App\Models\Transaction();
                $invoicePayment->user_id     = $invoice->customer_id;
                $invoicePayment->user_type   = 'Customer';
                $invoicePayment->type        = 'PAYPAL';
                $invoicePayment->created_by  = \Auth::check() ? \Auth::user()->id : $invoice->customer_id;
                $invoicePayment->payment_id  = $invoicePayment->id;
                $invoicePayment->category    = 'Invoice';
                $invoicePayment->amount      = $amount;
                $invoicePayment->date        = date('Y-m-d');
                $invoicePayment->created_by  = \Auth::check() ? \Auth::user()->creatorId() : $invoice->created_by;
                $invoicePayment->payment_id  = $payments->id;
                $invoicePayment->description = 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                $invoicePayment->account     = 0;

                \App\Models\Transaction::addTransaction($invoicePayment);

       
      
                if (Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice->id))->with('success', __('Payment successfully added.'));
                } else {
                    return redirect()->back()->with('success', __(' Payment successfully added.'));
                }
        
        } catch (\Exception $e) {
            if (Auth::check()) {
                return redirect()->route('invoice.show', \Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed.'));
            } else {
                return redirect()->back()->with('success', __('Transaction has been complted.'));
            }
        }
    }
}

