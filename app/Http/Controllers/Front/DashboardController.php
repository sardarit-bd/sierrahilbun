<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $recentOrders = Order::query()
            ->forUser($user->id)
            ->with(['items'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (Order $order) => [
                'id'              => $order->id,
                'total_amount'    => $order->total_amount,
                'status'          => $order->status,
                'delivery_status' => $order->delivery_status,
                'created_at'      => $order->created_at->toDateTimeString(),
                'items_count'     => $order->items->count(),
            ]);

        $articles = BlogPost::query()
            ->where('is_published', true)
            ->with('category')
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn (BlogPost $post) => [
                'id'          => $post->id,
                'title'       => $post->title,
                'slug'        => $post->slug,
                'excerpt'     => $post->excerpt,
                'category'    => $post->category?->name,
                'image_url'   => $post->featured_image_url,
                'image_alt'   => $post->featured_image_alt,
                'published_at'=> $post->published_at?->diffForHumans(),
            ]);

        return Inertia::render('dashboard', [
            'user' => [
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at->format('F j, Y'),
            ],
            'stats' => [
                'total_orders' => Order::forUser($user->id)->count(),
            ],
            'recent_orders' => $recentOrders,
            'articles'      => $articles,
        ]);
    }
}