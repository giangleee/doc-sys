<?php


namespace App\Repositories;


use App\Exceptions\ValidationDateException;
use App\Models\Attribute;
use App\Models\DocumentAttribute;
use Carbon\Carbon;

class DocumentAttributeRepository extends BaseRepository
{
    public $error;
    public function getModel()
    {
        return DocumentAttribute::class;
    }

    public function saveDocumentAttribute($attributeValues, $document)
    {
        $document->attributes()->detach();
        $attributeValues = json_decode($attributeValues, 1);
        $data = [];
        $executionDate = null;
        $validityPeriodEndDate = null;
        $this->error = [];
        foreach ($attributeValues as $attribute) {
            $value = $startDate = $endDate = null;
            $attrId = $attribute['attribute_id'];
            $attributeRepo = new AttributeRepository();
            $codeAttr = $attributeRepo->findOrFail($attrId)->code;
            if (!is_array($attribute['values'])) {
                if (empty($attribute['values'])) {
                    continue;
                }
                $value = $attribute['values'];
            } else {
                if (!is_array($attribute['values'][0])) {
                    if (empty($attribute['values'][0])) {
                        continue;
                    }
                    $value = implode(',', $attribute['values']);
                    if ($codeAttr == Attribute::EXECUTION_DATE) {
                        $executionDate = $value;
                    }
                } else {

                    $startDate = (isset($attribute['values'][0]['start']) && !empty($attribute['values'][0]['start']))
                        ? $attribute['values'][0]['start']
                        : null;
                    $endDate = (isset($attribute['values'][0]['end']) && !empty($attribute['values'][0]['end']))
                        ? $attribute['values'][0]['end']
                        : null;
                    if (!isset($startDate) && !isset($endDate)) {
                        continue;
                    }
                    if ((!empty($startDate) && !empty($endDate)) && (strtotime($startDate) > strtotime($endDate))) {
                        $this->error['validityPeriodEndDate'] = __('message.validateDateDocument.date');
                    }
                    if ($codeAttr == Attribute::VALIDITY_PERIOD) {
                        $validityPeriodEndDate = $endDate;
                    }
                }
            }
            $data[] = [
                'document_id' => $document->id,
                'attribute_id' => $attribute['attribute_id'],
                'value' => $value,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        if (isset($executionDate) && isset($validityPeriodEndDate)) {
            if (strtotime($executionDate) > strtotime($validityPeriodEndDate)) {
                $this->error['executionDate'] = __('message.validateDateDocument.executionDate');
            }
        }
        if (!empty($this->error)) {
            throw new ValidationDateException(__('message.data_invalid'));
        }
        if (count($data)) {
            $this->insertMany($data);
        }
    }

    public function getDocumentWithPeriod($ids)
    {
        return $this->model->whereIn('document_id', $ids)
            ->where('attribute_id', function ($query) {
                $query->select('id')
                    ->from(with(new Attribute)->getTable())
                    ->where('code', Attribute::VALIDITY_PERIOD);
            })
            ->whereNotNull('end_date')
            ->get();
    }

    public function getValuePeriod($documentID)
    {
        return $this->model->where('document_id', $documentID)
            ->where('attribute_id', function ($query) {
                $query->select('id')
                    ->from(with(new Attribute)->getTable())
                    ->where('code', Attribute::VALIDITY_PERIOD);
            })
            ->first();
    }
}
