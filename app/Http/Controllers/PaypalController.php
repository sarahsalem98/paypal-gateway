<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Items;
use Exception;
use Illuminate\Http\Request;
use  Srmklive\PayPal\Services\ExpressCheckout;
class PaypalController extends Controller
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new ExpressCheckout();
    }


    public function index(Request $request)
    {
        $response = [];
        if (session()->has('code')) {
            $response['code'] = session()->get('code');
            session()->forget('code');
        }
        
        if (session()->has('message')) {
            $response['message'] = session()->get('message');
            session()->forget('message');
        }
     
        return view('welcome', ["response"=>$response]);
    }




    public function getExpressCheckout(Request $request)
    {
        

        $cart = $this->getCheckoutData();

        try {
            $response = $this->provider->setExpressCheckout($cart);
              //dd($response);
             return redirect($response['paypal_link']);
        } catch (Exception $e) {
            $invoice = $this->createInvoice($cart, 'Invalid');
            session()->put(['code' => 'danger', 'message' => "Error processing PayPal payment for Order $invoice->id!"]);
        }
    }



    public function getExpressCheckoutSuccess(Request $request)
    {
    
        
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');

        $cart = $this->getCheckoutData();

        // Verify Express Checkout Token
        $response = $this->provider->getExpressCheckoutDetails($token);
               // dd($response);
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
                $payment_status = $this->provider->doExpressCheckoutPayment($cart, $token, $PayerID);
                $status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];
                $invoice = $this->createInvoice($cart, $status);
                   //dd($invoice);
            if ($invoice->paid) {
                session()->put(['code' => 'success', 'message' => "Order $invoice->id has been paid successfully!"]);
            } else {
                session()->put(['code' => 'danger', 'message' => "Error processing PayPal payment for Order $invoice->id!"]);
            }

            return redirect('/');
        }
    }



    protected function getCheckoutData()
    {
        $data = [];

        $order_id = Invoice::all()->count()+1;

       
            $data['items'] = [
                [
                    'name'  => 'Product1',
                    'price' => 9.99,
                    'qty'   => 1,
                ],
                [
                    'name'  => 'Product2',
                    'price' => 4.99,
                    'qty'   => 1,
                ],
            ];

        $data['return_url'] = url('/paypal/ec-checkout-success');
        
        $data['invoice_id'] = config('paypal.invoice_prefix').'_'.$order_id;
        $data['invoice_description'] = "Order #$order_id Invoice";
        $data['cancel_url'] = url('/');

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $data['total'] = $total;

        return $data;
    }



    protected function createInvoice($cart, $status)
    {
        $invoice = new Invoice();
        $invoice->title = $cart['invoice_description'];
        $invoice->price = $cart['total'];
        if (!strcasecmp($status, 'Completed') || !strcasecmp($status, 'Processed')) {
            $invoice->paid = 1;
        } else {
            $invoice->paid = 0;
        }
        $invoice->save();

        collect($cart['items'])->each(function ($product) use ($invoice) {
            $item = new Items();
            $item->invoice_id = $invoice->id;
            $item->item_name = $product['name'];
            $item->item_price = $product['price'];
            $item->item_qty = $product['qty'];

            $item->save();
        });

        return $invoice;
    }

}
