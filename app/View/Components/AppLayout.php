<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Render the authenticated app layout.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}

