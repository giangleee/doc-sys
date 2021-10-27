<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ErrorSheet implements FromView, WithTitle
{
    private $data;
    private $title;

    public function __construct($data, $title)
    {
        $this->data = $data;
        $this->title  = $title;
    }

    public function view(): View
    {
        return view('exports.error', [
            'data' => $this->data
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
