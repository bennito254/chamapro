<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the public marketing landing page for ChamaPro.
 */
class WelcomeController extends Controller
{
    /**
     * Display the public landing page.
     */
    public function __invoke(): Response
    {
        return Inertia::render('welcome');
    }
}
