<?php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomAlert extends Component
{
    public string $type;
    public string $title;
    public string $message;

    public function __construct(string $type = 'success', string $title, string $message)
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
    }

    public function render(): View|Closure|string
    {
        return view('components.custom-alert');
    }
}