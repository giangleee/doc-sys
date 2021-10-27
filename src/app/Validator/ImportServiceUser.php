<?php
namespace App\Validator;

use Illuminate\Support\Facades\Validator;
use App\Helper\Constant;
use App\Imports\OfficeImport;

class ImportServiceUser
{
    public static function validation($request)
    {
        $response = [
            'message' => '',
            'errors' => [],
            'data' => []
        ];

        foreach ($request->file('files') as $item) {
            //get extension file
            $filename = $item->getClientOriginalName();
            $infoFile = pathinfo($filename);

            if ($infoFile['extension'] == 'xls' || $infoFile['extension'] == 'xlsx') {
                $officeData = (new OfficeImport)->toArray($item);
                $totalColumn = count($officeData[0][0]);
                
                if (
                    $totalColumn != Constant::TOTAL_COLUMN_FILE_OFFICE
                    || isset($response['data']['office'])
                    ) {
                    $response['message'] = __('message.imports.data_invalid');
                    $response['errors']['files'][] = __('message.service_user.import_service_user', [
                        'filename' => $filename,
                    ]);
                } else {
                    $response['data']['office'] = $filename;
                }
            }

            if ($infoFile['extension'] == 'csv') {
                $handel = fopen($item->getRealPath(), 'r');
                if ($handel !== false) {
                    $totalColumn = count(fgetcsv($handel));
                }

                if (
                    $totalColumn != Constant::TOTAL_COLUMN_FILE_SERVICE_USER
                    || isset($response['data']['service_user'])
                ) {
                    $response['message'] = __('message.imports.data_invalid');
                    $response['errors']['files'][] = __('message.service_user.import_service_user', [
                        'filename' => $filename,
                    ]);
                } else {
                    $response['data']['service_user'] = $filename;
                }
            }
        }

        return $response;
    }
}
