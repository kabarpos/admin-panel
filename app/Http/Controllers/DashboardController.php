<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Pastikan user memiliki notifikasi
        $unreadNotifications = 0;
        if (method_exists($user, 'unreadNotifications')) {
            $unreadNotifications = $user->unreadNotifications()->count();
        }
        
        return Inertia::render('Dashboard/Index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
            ],
            'unreadNotifications' => $unreadNotifications,
            'recentActivities' => Activity::causedBy($user)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'created_at' => $activity->created_at->diffForHumans(),
                        'icon' => $this->getActivityIcon($activity->description),
                    ];
                }),
        ]);
    }

    private function getActivityIcon($description)
    {
        $icons = [
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'logged in' => 'login',
            'default' => 'bell',
        ];

        foreach ($icons as $key => $icon) {
            if (str_contains(strtolower($description), $key)) {
                return $icon;
            }
        }

        return $icons['default'];
    }
}
