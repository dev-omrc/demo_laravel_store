<?php

namespace App\Http\Controllers;


use App\Http\Requests\CheckoutRequest;
use Konekt\Address\Models\CountryProxy;
use Vanilo\Cart\Facades\Cart;
use Vanilo\Checkout\Facades\Checkout;
use Vanilo\Order\Contracts\OrderFactory;

/***************************************/
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Redirect;
use Session;
use URL;

class CheckoutController extends Controller
{

    public function __construct()
    {
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function show()
    {

        $checkout = false;

        if (Cart::isNotEmpty()) {
            $checkout = Checkout::getFacadeRoot();
            if ($old = old()) {
                $checkout->update($old);
            }

            $checkout->setCart(Cart::model());
        }

        return view('checkout.show', [
            'checkout'  => $checkout,
            'countries' => CountryProxy::all()
        ]);
    }

    public function submit(CheckoutRequest $request, OrderFactory $orderFactory)
    {
        $checkout = Checkout::getFacadeRoot();
        $checkout->update($request->all());
        $checkout->setCart(Cart::model());

        $payment_method = $request->payment_method;

        switch ($payment_method){
            case 'paypal':
                $this->pay_with_paypal($checkout, $orderFactory);
                break;
        }

        Cart::destroy();
        return redirect()->route('shop.index');
    }

    public function pay_with_paypal($checkout, OrderFactory $orderFactory)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $items = array();
        foreach (Cart::getItems() as $cart_item){
            $item = new Item();
            $item->setName($cart_item->product->getName())
                ->setCurrency('USD')
                ->setQuantity($cart_item->quantity)
                ->setPrice($cart_item->price);
            array_push($items, $item);
        }
        $items_list = new ItemList();
        $items_list->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency('USD')->setTotal(Cart::total());

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($items_list)
            ->setDescription('Demo Laravel Store para FLISOL 2018 UGB');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(route('cart.show'))
            ->setCancelUrl(route('cart.show'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try{
            $payment->create($this->_api_context);
        }catch(\Paypal\Exception\PayPalConnectionException $ex){
            if (\Config::get('app.debug')){
                flash("Error, connection timeout");
            }else{
                flash("Algo ocurrió, disculpe las molestias.");
            }
            return Redirect::to('shop/index');
        }

        foreach ($payment->getLinks() as $link){
            if ($link->getRel() == 'approval_url'){
                $redirect_url = $link->getHref();
                break;
            }
        }


        $order_id = $payment->getId();
        $order = $orderFactory->createFromCheckout($checkout);
        $order->number = $order_id;

        if (isset($redirect_url)){
            $order->save();
            flash("Order created: " . $order->getNumber());
            return Redirect::away($redirect_url)->send();
        }
        flash("Ha ocurrido un error.");
                return Redirect::route('shop.index');
    }


}
