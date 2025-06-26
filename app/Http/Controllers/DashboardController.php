<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter atau search jika ada
        $query = Question::with(['user', 'hashtags'])
            ->withCount(['comments', 'likes']);

        // (Opsional) filter berdasarkan tab/filter jika ada (terbaru, terbanyak, dsb)
        if ($request->filter === 'terbanyak') {
            $query->orderByDesc('comments_count');
        } elseif ($request->filter === 'baru-dijawab') {
            $query->whereHas('comments')->orderByDesc('updated_at');
        } elseif ($request->filter === 'belum-dijawab') {
            $query->doesntHave('comments')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        // (Opsional) search
        if ($request->has('search') && trim($request->search) !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                ->orWhere('content', 'like', "%$search%");
            });
        }

        // PAGINATE!
        $questions = $query->paginate(10);

        // (Opsional) popular tags dummy
        $popularTags = ['laravel','tailwind','php','javascript','react','vue','css','html','mysql','api','auth','livewire'];

        // === Tambahkan: Ambil notifikasi terbaru (10) untuk user login ===
        if (Auth::check()) {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->take(10)
                ->get();
            $global_notifications = Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $notifications = [];
            $global_notifications = [];
        }
        return view('dashboard', compact('questions', 'popularTags', 'notifications', 'global_notifications'));
    }

    /**
     * Tampilkan dashboard untuk admin.
     */
    public function adminDashboard(Request $request)
    {
        return view('admin.dashboard');
    }
}