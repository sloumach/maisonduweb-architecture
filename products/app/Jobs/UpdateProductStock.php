<?php

namespace App\Jobs;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateProductStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function fire($job, $data)
    {

        \Log::info('Starting job with data:', ['data' => $data]);
        $items = $data['data'];
        \Log::info('Processing items', ['items' => $items]);

        foreach ($items as $item) {
            // Log chaque item pour vérification
            \Log::info('Processing item', ['item' => $item]);

            // Traitement de chaque produit
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('quantity', $item['quantity']);
            }
        }

       /*  \Log::info('Order Data received:', $this->orderData);
        $product = Product::find($this->orderData['product_id']);
        if ($product) {
            $product->decrement('stock', $this->orderData['quantity']);
        } */
    }
}
