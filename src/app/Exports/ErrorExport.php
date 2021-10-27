<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\ErrorSheet;

class ErrorExport implements WithMultipleSheets
{
    protected $errorOffice;
    protected $errorServiceUser;

    public function __construct($errorOffice, $errorServiceUser)
    {
        $this->errorOffice = $errorOffice;
        $this->errorServiceUser = $errorServiceUser;
    }

    public function sheets(): array
    {
        //format error log
        $errorOffice = [];
        foreach ($this->errorOffice as $k => $v) {
            $errorOffice[$k] = '';
            foreach ($v as $value) {
                $errorOffice[$k] .= '<br>' . implode('<br>', $value);
            }
            $errorOffice[$k] = trim($errorOffice[$k], '<br>');
        }

        $errorServiceUser = [];
        foreach ($this->errorServiceUser as $k => $v) {
            $errorServiceUser[$k] = '';
            foreach ($v as $value) {
                $errorServiceUser[$k] .= '<br>' . implode('<br>', $value);
            }
            $errorServiceUser[$k] = trim($errorServiceUser[$k], '<br>');
        }
        return [
            new ErrorSheet($errorOffice, __('message.export.title_office')),
            new ErrorSheet($errorServiceUser, __('message.export.title_service_user'))
        ];
    }
}
