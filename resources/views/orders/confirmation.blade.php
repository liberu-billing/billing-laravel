<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Confirmation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Thank you for your order!</h3>
                    <p class="mt-1 text-sm text-gray-600">Your order has been successfully placed. Here are the details:</p>

                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900">Order Information</h4>
                        <dl class="mt-2 border-t border-b border-gray-100">
                            <div class="py-3 flex justify-between text-sm font-medium">
                                <dt class="text-gray-500">Order Number:</dt>
                                <dd class="text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm font-medium">
                                <dt class="text-gray-500">Order Date:</dt>
                                <dd class="text-gray-900">{{ $invoice->issue_date->format('F j, Y') }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm font-medium">
                                <dt class="text-gray-500">Total Amount:</dt>
                                <dd class="text-gray-900">{{ $invoice->currency->code }} {{ number_format($invoice->total_amount, 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900">Customer Information</h4>
                        <dl class="mt-2 border-t border-b border-gray-100">
                            <div class="py-3 flex justify-between text-sm font-medium">
                                <dt class="text-gray-500">Name:</dt>
                                <dd class="text-gray-900">{{ $invoice->customer->name }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm font-medium">
                                <dt class="text-gray-500">Email:</dt>
                                <dd class="text-gray-900">{{ $invoice->customer->email }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900">Order Details</h4>
                        <table class="mt-2 min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->productService->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->currency->code }} {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->currency->code }} {{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            {{ __('Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>