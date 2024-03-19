<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Exception;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

    function SalePage(){
        return view('pages.dashboard.sale-page');
    }


    function InvoicePage(){
        return view('pages.dashboard.invoice-page');
    }


    function invoiceCreate(Request $request){

        DB::beginTransaction();
        try {    
                    
            // dd($request->header('id'));
            $user_id = $request->header('id');
            // dd($user_id);
            
            $total = $request->input('total');
            $discount = $request->input('discount');
            $vat = $request->input('vat');
            $payable = $request->input('payable');
            $customer_id = $request->input('customer_id');
            
            $invoice = Invoice::create([

                'total' => $total,
                'discount' => $discount,
                'vat' => $vat,
                'payable' => $payable,
                'customer_id' => $customer_id,
                'user_id' => $user_id

            ]);
           
            $invoice_id = $invoice->id;
            $products = $request->input('products');
            
            foreach($products as $product){
                InvoiceProduct::create([
                    'invoice_id' => $invoice_id,
                    'user_id' => $user_id,
                    'product_id' => $product['product_id'],
                    'qty' => $product['qty'],
                    'sale_price' => $product['sale_price']
                ]);
            };
            // dd()

            DB::commit();
            return 1;

        } catch (Exception $e) {
            DB::rollBack();
            return 0;
        }        
    }


//     public function InvoiceCreate(Request $request, Invoice $invoice, InvoiceProduct $invoiceProduct)
// {
//     DB::beginTransaction();
//     try {
//         $user_id = $request->header('id');
//         $customer_id = $request->input('customer_id');
//         $multidimensionalArray = $request->input('products');

//         dd($request);

//         $validatedData = $this->validate($request, [
//             'total' => 'required',
//             'discount' => 'nullable',
//             'vat' => 'nullable',
//             'payable' => 'nullable',
//         ]);

//         // Create the invoice
//         $createdInvoice = $invoice->create([
//             'total' => $validatedData['total'],
//             'discount' => $validatedData['discount'],
//             'vat' => $validatedData['vat'],
//             'payable' => $validatedData['payable'],
//             'customer_id' => $customer_id,
//             'user_id' => $user_id,
//         ]);

//         // Attach the invoice to the customer
//         $customer = Customer::find($customer_id);
//         $customer->invoices()->attach($createdInvoice->id);

//         // Create invoice products
//         foreach ($multidimensionalArray as $product) {
//             $invoiceProductData = [
//                 'customer_id' => $customer_id,
//                 'product_id' => $product['product_id'],
//                 'user_id' => $user_id,
//                 'invoice_id' => $createdInvoice->id,
//                 'qty' => $product['qty'],
//                 'sale_price' => $product['sale_price'],
//             ];

//             // Create the invoice product
//             $invoiceProduct->create($invoiceProductData);
//         }

//         DB::commit();
//         return response()->json([
//             'success' => true,
//             'message' => 'Invoice created successfully',
//         ]);
//     } catch (Exception $exception) {
//         DB::rollBack();
//         return response()->json([
//             'success' => false,
//             'message' => $exception->getMessage(),
//         ]);
//     }
// }


    function invoiceSelect(Request $request){
        $user_id = $request->header('id');
        
        return Invoice::where('user_id', $user_id)->with('customer')->get();
    }


    function InvoiceDetails(Request $request){
        $user_id = $request->header('id');
        $customerDetails = Customer::where('user_id', $user_id)->where('id', $request->input('cus_id'))->first();
        $invoiceTotal = Invoice::where('user_id', $user_id)->where('id', $request->input('inv_id'))->first();
        $invoiceProduct = InvoiceProduct::where('user_id', $user_id)->where('invoice_id', $request->input('inv_id'))->with('product')->get();

        return array(
            'customer' => $customerDetails,
            'invoice' => $invoiceTotal,
            'product' => $invoiceProduct
        );
    }


    function invoiceDelete(Request $request){
        DB::beginTransaction();

        try {
            $user_id = $request->header('id');

            InvoiceProduct::where('user_id', $user_id)->where('invoice_id', $request->input('inv_id'))->delete();

            Invoice::where('id', $request->input('inv_id'))->delete();

            DB::commit();
            return 1;
        }
        catch(Exception $e) {
            DB::rollBack();
            return 0;
        }
    }
}
