<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Render the guest/public layout.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}

