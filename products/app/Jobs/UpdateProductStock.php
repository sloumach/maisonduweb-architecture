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
    $action = $data['action'];
    $items = $data['data'];  // Assurez-vous que ceci est toujours un tableau

    if (isset($items['product_id'])) { // S'il y a un 'product_id', c'est un seul item
        $items = [$items];  // Encapsulez le seul item dans un tableau
    }

    foreach ($items as $item) {


        // Vérifiez si $item est un tableau et contient 'product_id'
        if (is_array($item) && isset($item['product_id'])) {
            $product = Product::find($item['product_id']);
            if ($product) {

                if ($action === 'decrement') {
                    $product->decrement('quantity', $item['quantity']);
                } elseif ($action === 'increment') {
                    $product->increment('quantity', $item['quantity']);
                }
            }
        } else {
            \Log::error('Invalid item structure', ['item' => $item]);
            // Gérer l'erreur ou signaler un problème
        }
    }
}


}
